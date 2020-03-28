<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor;

use Innmind\IPC\Client;
use Innmind\Immutable\Sequence;

final class Store
{
    private Sequence $events;

    public function __construct()
    {
        $this->events = Sequence::of(Event::class);
    }

    public function remember(Event $event): void
    {
        $this->events = ($this->events)($event);
    }

    public function notify(Client $client): void
    {
        $this->events->foreach(
            static fn(Event $event) => $client->send($event->toMessage()),
        );
    }
}
