<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor;

use Innmind\IPC\Client;
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

    public function notify(Client $client): void
    {
        $this->events->reduce(
            $client,
            static function(Client $client, Event $event): Client {
                $client->send($event->toMessage());

                return $client;
            }
        );
    }
}
