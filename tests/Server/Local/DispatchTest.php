<?php
declare(strict_types = 1);

namespace Tests\Innmind\InstallationMonitor\Server\Local;

use Innmind\InstallationMonitor\{
    Server\Local\Dispatch,
    Store,
};
use Innmind\Socket\{
    Server\Connection,
    Event\ConnectionReceived,
    Event\ConnectionClosed,
    Event\DataReceived,
};
use Innmind\Stream\Exception\FailedToWriteToStream;
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;

class DispatchTest extends TestCase
{
    public function testBehaviour()
    {
        $dispatch = new Dispatch(new Store);
        $connection1 = $this->createMock(Connection::class);
        $connection2 = $this->createMock(Connection::class);
        $connection3 = $this->createMock(Connection::class);
        $connection4 = $this->createMock(Connection::class);
        $connection1
            ->expects($this->at(0))
            ->method('write')
            ->with(Str::of('{"name":"foo","payload":[]}'))
            ->will($this->throwException(new FailedToWriteToStream));
        $connection1
            ->expects($this->at(1))
            ->method('write')
            ->with(Str::of('{"name":"bar","payload":[]}'));
        $connection3
            ->expects($this->once())
            ->method('write')
            ->with(Str::of('{"name":"foo","payload":[]}'));
        $connection2
            ->expects($this->never())
            ->method('write');
        $connection4
            ->expects($this->at(0))
            ->method('write')
            ->with(Str::of('{"name":"foo","payload":[]}'));
        $connection4
            ->expects($this->at(1))
            ->method('write')
            ->with(Str::of('{"name":"bar","payload":[]}'));


        $this->assertNull($dispatch(new ConnectionReceived($connection1)));
        $this->assertNull($dispatch(new ConnectionReceived($connection2)));
        $this->assertNull($dispatch(new ConnectionReceived($connection3)));
        $this->assertNull($dispatch(new DataReceived(
            $connection2,
            Str::of('{"name":"foo","payload":[]}')
        )));
        $this->assertNull($dispatch(new ConnectionClosed($connection2)));
        $this->assertNull($dispatch(new DataReceived(
            $connection3,
            Str::of('{"name":"bar","payload":[]}')
        )));
        $this->assertNull($dispatch(new ConnectionReceived($connection4)));
    }
}
