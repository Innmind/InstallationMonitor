<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor;

use Innmind\Immutable\Stream;

final class Store
{
    private $events;

    public function __construct()
    {
        $this->events = Stream::of(Event::class);
    }

    public function remember(Event $event): void
    {
        $this->events = $this->events->add($event);
    }

    public function notify(IncomingConnection $connection): void
    {
        $connection->notify(...$this->events);
    }
}
