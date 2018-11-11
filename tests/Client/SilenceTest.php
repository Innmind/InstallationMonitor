<?php
declare(strict_types = 1);

namespace Tests\Innmind\InstallationMonitor\Client;

use Innmind\InstallationMonitor\{
    Client\Silence,
    Client\Socket,
    Client,
    Event,
};
use Innmind\Socket\{
    Address\Unix as Address,
    Client\Unix as UnixClient,
};
use Innmind\OperatingSystem\Sockets;
use Innmind\Immutable\{
    Map,
    Stream,
};
use PHPUnit\Framework\TestCase;

class SilenceTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Client::class,
            new Silence($this->createMock(Client::class))
        );
    }

    public function testSend()
    {
        $client = new Silence(
            $inner = $this->createMock(Client::class)
        );
        $event = new Event(
            new Event\Name('foo'),
            new Map('string', 'variable')
        );
        $inner
            ->expects($this->once())
            ->method('send')
            ->with($event);

        $this->assertNull($client->send($event));
    }

    public function testSilenceFailedSend()
    {
        $client = new Silence(
            new Socket(
                $this->createMock(Sockets::class),
                new Address('/tmp/unknown')
            )
        );

        $this->assertNull($client->send(new Event(
            new Event\Name('foo'),
            new Map('string', 'variable')
        )));
    }

    public function testEvents()
    {
        $client = new Silence(
            $inner = $this->createMock(Client::class)
        );
        $events = Stream::of(Event::class, new Event(
            new Event\Name('foo'),
            new Map('string', 'variable')
        ));
        $inner
            ->expects($this->once())
            ->method('events')
            ->willReturn($events);

        $this->assertSame($events, $client->events());
    }

    public function testSilenceFailedEventsRetrieval()
    {
        $client = new Silence(
            new Socket(
                $sockets = $this->createMock(Sockets::class),
                $address = new Address('/tmp/unknown')
            )
        );
        $sockets
            ->expects($this->once())
            ->method('connectTo')
            ->will($this->returnCallback(static function() use ($address) {
                return new UnixClient($address);
            }));

        $events = $client->events();

        $this->assertTrue($events->equals(Stream::of(Event::class)));
    }
}
