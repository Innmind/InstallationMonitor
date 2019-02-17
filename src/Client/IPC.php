<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor\Client;

use Innmind\InstallationMonitor\{
    Client,
    Event,
    IPC\Message\WaitingForEvents,
    IPC\Message\EndOfTransmission,
};
use Innmind\IPC\{
    IPC as IPCInterface,
    Process\Name,
    Message,
    Exception\Stop,
};
use Innmind\Immutable\{
    StreamInterface,
    Stream,
};
use Ramsey\Uuid\Uuid;

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

        $name = new Name((string) Uuid::uuid4());
        $this
            ->ipc
            ->get($this->server)
            ->send($name)(...$messages);
    }

    /**
     * {@inheritdoc}
     */
    public function events(): StreamInterface
    {
        if (!$this->ipc->exist($this->server)) {
            return Stream::of(Event::class);
        }

        $name = new Name((string) Uuid::uuid4());
        $this->ipc->get($this->server)->send($name)(new WaitingForEvents);

        $events = [];
        $this
            ->ipc
            ->listen($name)(static function(Message $message) use (&$events): void {
                if ((new EndOfTransmission)->equals($message)) {
                    throw new Stop;
                }

                $events[] = Event::from($message);
            });

        return Stream::of(Event::class, ...$events);
    }
}
