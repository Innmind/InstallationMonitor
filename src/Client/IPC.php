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
use Innmind\Immutable\Sequence;

final class IPC implements Client
{
    private IPCInterface $ipc;
    private Name $server;

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

    public function events(): Sequence
    {
        if (!$this->ipc->exist($this->server)) {
            return Sequence::of(Event::class);
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

        return Sequence::of(Event::class, ...$events);
    }
}
