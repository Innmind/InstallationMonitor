<?php
declare(strict_types = 1);

namespace Tests\Innmind\InstallationMonitor;

use Innmind\InstallationMonitor\{
    Events,
    Event,
};
use Innmind\Immutable\{
    Map,
    Str,
};
use PHPUnit\Framework\TestCase;

class EventsTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(\Iterator::class, new Events);
    }

    public function testIterator()
    {
        $events = new Events(
            $event1 = new Event(
                new Event\Name('foo'),
                new Map('string', 'variable')
            ),
            $event2 = new Event(
                new Event\Name('bar'),
                new Map('string', 'variable')
            )
        );

        $this->assertSame([$event1, $event2], iterator_to_array($events));
    }

    public function testToString()
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

        $string = $events->toString();

        $this->assertInstanceOf(Str::class, $string);
        $this->assertSame('ASCII', (string) $string->encoding());
        $this->assertSame(
            '{"name":"foo","payload":[]}ø{"name":"bar","payload":[]}',
            (string) $string
        );
    }

    public function testSize()
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

        $this->assertCount(2, $events);
    }

    public function testFrom()
    {
        $events = Events::from(Str::of(
            '{"name":"foo","payload":[]}ø{"name":"bar","payload":[]}'
        ));

        $this->assertInstanceOf(Events::class, $events);
        $this->assertEquals(
            $events,
            new Events(
                new Event(
                    new Event\Name('foo'),
                    new Map('string', 'variable')
                ),
                new Event(
                    new Event\Name('bar'),
                    new Map('string', 'variable')
                )
            )
        );
    }
}
