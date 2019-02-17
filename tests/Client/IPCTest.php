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
    Sender,
    Receiver,
    Exception\Stop,
};
use Innmind\Immutable\{
    Map,
    StreamInterface,
};
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
            new Map('string', 'variable')
        )));
    }

    public function testSend()
    {
        $server = new Name('foo');
        $event1 = new Event(
            new Event\Name('foo'),
            new Map('string', 'variable')
        );
        $event2 = new Event(
            new Event\Name('bar'),
            new Map('string', 'variable')
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
            ->expects($this->once())
            ->method('send')
            ->willReturn($sender = $this->createMock(Sender::class));
        $sender
            ->expects($this->once())
            ->method('__invoke')
            ->with($event1->toMessage(), $event2->toMessage());

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

        $this->assertInstanceOf(StreamInterface::class, $events);
        $this->assertSame(Event::class, (string) $events->type());
        $this->assertCount(0, $events);
    }

    public function testEvents()
    {
        $server = new Name('server');
        $event1 = new Event(
            new Event\Name('foo'),
            new Map('string', 'variable')
        );
        $event2 = new Event(
            new Event\Name('bar'),
            new Map('string', 'variable')
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
            ->expects($this->once())
            ->method('send')
            ->willReturn($sender = $this->createMock(Sender::class));
        $sender
            ->expects($this->once())
            ->method('__invoke')
            ->with(new WaitingForEvents);
        $ipc
            ->expects($this->at(2))
            ->method('listen')
            ->willReturn($receiver = $this->createMock(Receiver::class));
        $receiver
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(static function($listen) use ($event1, $event2): bool {
                $listen($event1->toMessage());
                $listen($event2->toMessage());

                try {
                    $listen(new EndOfTransmission);

                    return false;
                } catch (Stop $e) {
                    return true;
                }
            }));

        $events = $client->events();

        $this->assertInstanceOf(StreamInterface::class, $events);
        $this->assertSame(Event::class, (string) $events->type());
        $this->assertCount(2, $events);
        $this->assertEquals([$event1, $event2], $events->toPrimitive());
    }
}
