<?php
declare(strict_types = 1);

namespace Tests\Innmind\InstallationMonitor\Client;

use Innmind\InstallationMonitor\{
    Client\IPC,
    Client,
    Event,
    IPC\Message\WaitingForEvents,
    IPC\Message\EndOfTransmission,
};
use Innmind\IPC\{
    IPC as IPCInterface,
    Process,
    Process\Name,
    Exception\ConnectionClosed,
};
use Innmind\Immutable\{
    Map,
    Sequence,
};
use function Innmind\Immutable\unwrap;
use PHPUnit\Framework\TestCase;

class IPCTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Client::class,
            new IPC(
                $this->createMock(IPCInterface::class),
                new Name('foo')
            )
        );
    }

    public function testDoesntSendWhenNoServer()
    {
        $client = new IPC(
            $ipc = $this->createMock(IPCInterface::class),
            $server = new Name('server')
        );
        $ipc
            ->expects($this->once())
            ->method('exist')
            ->with($server)
            ->willReturn(false);

        $this->assertNull($client->send(new Event(
            new Event\Name('foo'),
            Map::of('string', 'variable')
        )));
    }

    public function testSend()
    {
        $server = new Name('foo');
        $event1 = new Event(
            new Event\Name('foo'),
            Map::of('string', 'variable')
        );
        $event2 = new Event(
            new Event\Name('bar'),
            Map::of('string', 'variable')
        );

        $client = new IPC(
            $ipc = $this->createMock(IPCInterface::class),
            $server
        );
        $ipc
            ->expects($this->at(0))
            ->method('exist')
            ->with($server)
            ->willReturn(true);
        $ipc
            ->expects($this->at(1))
            ->method('get')
            ->with($server)
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->at(0))
            ->method('send')
            ->with($event1->toMessage(), $event2->toMessage());
        $process
            ->expects($this->at(1))
            ->method('close');

        $this->assertNull($client->send($event1, $event2));
    }

    public function testReturnEmptyStreamWhenNoServer()
    {
        $client = new IPC(
            $ipc = $this->createMock(IPCInterface::class),
            $server = new Name('server')
        );
        $ipc
            ->expects($this->once())
            ->method('exist')
            ->with($server)
            ->willReturn(false);

        $events = $client->events();

        $this->assertInstanceOf(Sequence::class, $events);
        $this->assertSame(Event::class, (string) $events->type());
        $this->assertCount(0, $events);
    }

    public function testEvents()
    {
        $server = new Name('server');
        $event1 = new Event(
            new Event\Name('foo'),
            Map::of('string', 'variable')
        );
        $event2 = new Event(
            new Event\Name('bar'),
            Map::of('string', 'variable')
        );

        $client = new IPC(
            $ipc = $this->createMock(IPCInterface::class),
            $server
        );
        $ipc
            ->expects($this->at(0))
            ->method('exist')
            ->with($server)
            ->willReturn(true);
        $ipc
            ->expects($this->at(1))
            ->method('get')
            ->with($server)
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->at(0))
            ->method('send')
            ->with(new WaitingForEvents);
        $process
            ->expects($this->at(1))
            ->method('wait')
            ->willReturn($event1->toMessage());
        $process
            ->expects($this->at(2))
            ->method('wait')
            ->willReturn($event2->toMessage());
        $process
            ->expects($this->at(3))
            ->method('wait')
            ->will($this->throwException(new ConnectionClosed));

        $events = $client->events();

        $this->assertInstanceOf(Sequence::class, $events);
        $this->assertSame(Event::class, (string) $events->type());
        $this->assertCount(2, $events);
        $this->assertEquals([$event1, $event2], unwrap($events));
    }
}
