<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor\IncomingConnection;

use Innmind\InstallationMonitor\{
    IncomingConnection,
    Event,
    Events,
};
use Innmind\Socket\Server\Connection;

final class Socket implements IncomingConnection
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function notify(Event ...$events): void
    {
        $events = new Events(...$events);

        if ($events->count() === 0) {
            return;
        }

        $this->connection->write($events->toString());
    }
}
