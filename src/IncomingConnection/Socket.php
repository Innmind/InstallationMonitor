<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor\IncomingConnection;

use Innmind\InstallationMonitor\{
    IncomingConnection,
    Event,
    Serialize,
};
use Innmind\Socket\Server\Connection;

final class Socket implements IncomingConnection
{
    private $connection;
    private $serialize;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->serialize = new Serialize;
    }

    public function notify(Event ...$events): void
    {
        foreach ($events as $event) {
            $this->connection->write(
                ($this->serialize)($event)
            );
        }
    }
}
