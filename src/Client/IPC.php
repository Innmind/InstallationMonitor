<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor\Client;

use Innmind\InstallationMonitor\{
    Client,
    Event,
    IPC\Message\WaitingForEvents,
};
use Innmind\IPC\{
    IPC as IPCInterface,
    Process\Name,
    Message,
    Exception\ConnectionClosed,
};
use Innmind\Immutable\{
    StreamInterface,
    Stream,
};

final class IPC implements Client
{
    private $ipc;
    private $server;

    public function __construct(IPCInterface $ipc, Name $server)
    {
        $this->ipc = $ipc;
        $this->server = $server;
    }

    public function send(Event ...$events): void
    {
        if (!$this->ipc->exist($this->server)) {
            return;
        }

        $messages = [];

        foreach ($events as $event) {
            $messages[] = $event->toMessage();
        }

        $process = $this->ipc->get($this->server);
        $process->send(...$messages);
        $process->close();
    }

    /**
     * {@inheritdoc}
     */
    public function events(): StreamInterface
    {
        if (!$this->ipc->exist($this->server)) {
            return Stream::of(Event::class);
        }

        $process = $this->ipc->get($this->server);
        $process->send(new WaitingForEvents);

        $events = [];

        try {
            while (true) {
                $events[] = Event::from($process->wait());
            }
        } catch (ConnectionClosed $e) {
            // end of transmission
        }

        return Stream::of(Event::class, ...$events);
    }
}
