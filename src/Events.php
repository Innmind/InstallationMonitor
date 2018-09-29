<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor;

use Innmind\Socket\Server\Connection;
use Innmind\Immutable\{
    Stream,
    Str,
};

final class Events
{
    private $serialize;
    private $unserialize;
    private $events;

    public function __construct(Event ...$events)
    {
        $this->serialize = new Serialize;
        $this->unserialize = new Unserialize;
        $this->events = Stream::of(Event::class, ...$events);
    }

    public function send(Connection $connection): void
    {
        $connection->write(Str::of("incoming_events:{$this->events->size()}\n"));
        $this->events->reduce(
            $connection,
            function(Connection $connection, Event $event): Connection {
                return $connection->write(
                    ($this->serialize)($event)->append("\n")
                );
            }
        );
    }
}
