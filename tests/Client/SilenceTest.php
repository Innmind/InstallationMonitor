<?php
declare(strict_types = 1);

namespace Tests\Innmind\InstallationMonitor\Client;

use Innmind\InstallationMonitor\{
    Client\Silence,
    Client,
    Event,
};
use Innmind\Immutable\{
    Map,
    Sequence,
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
            Map::of('string', 'variable')
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
            $inner = $this->createMock(Client::class)
        );
        $inner
            ->expects($this->once())
            ->method('send')
            ->will($this->throwException(new \RuntimeException));

        $this->assertNull($client->send(new Event(
            new Event\Name('foo'),
            Map::of('string', 'variable')
        )));
    }

    public function testEvents()
    {
        $client = new Silence(
            $inner = $this->createMock(Client::class)
        );
        $events = Sequence::of(Event::class, new Event(
            new Event\Name('foo'),
            Map::of('string', 'variable')
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
            $inner = $this->createMock(Client::class)
        );
        $inner
            ->expects($this->once())
            ->method('events')
            ->will($this->throwException(new \RuntimeException));

        $events = $client->events();

        $this->assertTrue($events->equals(Sequence::of(Event::class)));
    }
}
