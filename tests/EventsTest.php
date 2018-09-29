<?php
declare(strict_types = 1);

namespace Tests\Innmind\InstallationMonitor;

use Innmind\InstallationMonitor\{
    Events,
    Event,
};
use Innmind\Socket\Server\Connection;
use Innmind\Immutable\{
    Map,
    Str,
};
use PHPUnit\Framework\TestCase;

class EventsTest extends TestCase
{
    public function testSend()
    {
        $events = new Events(
            new Event(
                new Event\Name('foo'),
                new Map('string', 'variable')
            ),
            new Event(
                new Event\Name('bar'),
                new Map('string', 'variable')
            )
        );
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->at(0))
            ->method('write')
            ->with(Str::of("incoming_events:2\n"))
            ->will($this->returnSelf());
        $connection
            ->expects($this->at(1))
            ->method('write')
            ->with(Str::of('{"name":"foo","payload":[]}'."\n"))
            ->will($this->returnSelf());
        $connection
            ->expects($this->at(2))
            ->method('write')
            ->with(Str::of('{"name":"bar","payload":[]}'."\n"))
            ->will($this->returnSelf());

        $this->assertNull($events->send($connection));
    }
}
