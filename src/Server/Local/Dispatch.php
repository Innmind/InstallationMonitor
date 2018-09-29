<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor\Server\Local;

use Innmind\InstallationMonitor\{
    Store,
    Unserialize,
    IncomingConnection\Socket,
    Event,
};
use Innmind\Socket\{
    Server\Connection,
    Event\ConnectionReceived,
    Event\ConnectionClosed,
    Event\DataReceived,
};
use Innmind\Immutable\Set;

final class Dispatch
{
    private $store;
    private $unserialize;
    private $connections;

    public function __construct(Store $store)
    {
        $this->store = $store;
        $this->unserialize = new Unserialize;
        $this->connections = Set::of(Connection::class);
    }

    public function __invoke($event): void
    {
        switch (get_class($event)) {
            case ConnectionReceived::class:
                $this->connections = $this->connections->add($event->connection());
                $this->store->notify(
                    new Socket($event->connection())
                );
                break;

            case ConnectionClosed::class:
                $this->connections = $this->connections->remove($event->connection());
                break;

            case DataReceived::class:
                $source = $event->source();
                $event = ($this->unserialize)($event->data());
                $this->store->remember($event);
                $this
                    ->connections
                    ->remove($source)
                    ->reduce(
                        $event,
                        static function(Event $event, Connection $connection): Event {
                            try {
                                (new Socket($connection))->notify($event);
                            } catch (\Throwable $e) {
                                // no matter the error do not prevent the other
                                // connections to receive the events
                            }

                            return $event;
                        }
                    );
                break;
        }
    }
}
