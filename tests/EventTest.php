<?php
declare(strict_types = 1);

namespace Tests\Innmind\InstallationMonitor;

use Innmind\InstallationMonitor\{
    Event,
    Event\Name,
};
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public function testInterface()
    {
        $event = new Event(
            $name = new Name('foo'),
            $payload = new Map('string', 'variable')
        );

        $this->assertSame($name, $event->name());
        $this->assertSame($payload, $event->payload());
    }

    public function testThrowWhenInvalidPayloadKey()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 2 must be of type MapInterface<string, variable>');

        new Event(new Name('foo'), new Map('scalar', 'variable'));
    }

    public function testThrowWhenInvalidPayloadValue()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 2 must be of type MapInterface<string, variable>');

        new Event(new Name('foo'), new Map('string', 'scalar'));
    }
}
