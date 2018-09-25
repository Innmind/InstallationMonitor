<?php
declare(strict_types = 1);

namespace Tests\Innmind\GuiltySparkMonitor;

use Innmind\GuiltySparkMonitor\{
    Serialize,
    Event,
    Event\Name,
};
use Innmind\Immutable\{
    Map,
    Str,
};
use PHPUnit\Framework\TestCase;

class SerializeTest extends TestCase
{
    public function testInterface()
    {
        $serialize = new Serialize;

        $string = $serialize(new Event(
            new Name('foo'),
            (new Map('string', 'variable'))
                ->put('foo', 42)
                ->put('bar', 'baz')
        ));

        $this->assertInstanceOf(Str::class, $string);
        $this->assertSame(
            '{"name":"foo","payload":{"foo":42,"bar":"baz"}}',
            (string) $string
        );
    }
}
