<?php
declare(strict_types = 1);

namespace Tests\Innmind\InstallationMonitor\IncomingConnection;

use Innmind\InstallationMonitor\{
    IncomingConnection\Socket,
    IncomingConnection,
    Event,
};
use Innmind\Socket\Server\Connection;
use Innmind\Immutable\{
    Map,
    Str,
};
use PHPUnit\Framework\TestCase;

class SocketTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            IncomingConnection::class,
            new Socket($this->createMock(Connection::class))
        );
    }

    public function testNotify()
    {
        $socket = new Socket(
            $connection = $this->createMock(Connection::class)
        );
        $connection
            ->expects($this->at(0))
            ->method('write')
            ->with(Str::of('{"name":"foo","payload":[]}'))
            ->will($this->returnSelf());
        $connection
            ->expects($this->at(1))
            ->method('write')
            ->with(Str::of('{"name":"bar","payload":[]}'))
            ->will($this->returnSelf());

        $this->assertNull($socket->notify(
            new Event(
                new Event\Name('foo'),
                new Map('string', 'variable')
            ),
            new Event(
                new Event\Name('bar'),
                new Map('string', 'variable')
            )
        ));
    }
}
