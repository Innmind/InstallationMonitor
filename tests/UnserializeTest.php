<?php
declare(strict_types = 1);

namespace Tests\Innmind\InstallationMonitor;

use Innmind\InstallationMonitor\{
    Unserialize,
    Event,
    Exception\DomainException,
};
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;

class UnserializeTest extends TestCase
{
    public function testInvokation()
    {
        $unserialize = new Unserialize;

        $event = $unserialize(Str::of('{"name":"foo","payload":{"foo":42,"bar":"baz"}}'));

        $this->assertInstanceOf(Event::class, $event);
        $this->assertSame('foo', (string) $event->name());
        $this->assertSame('string', (string) $event->payload()->keyType());
        $this->assertSame('variable', (string) $event->payload()->valueType());
        $this->assertCount(2, $event->payload());
        $this->assertSame(42, $event->payload()->get('foo'));
        $this->assertSame('baz', $event->payload()->get('bar'));
    }

    public function testThrowWhenNoName()
    {
        $unserialize = new Unserialize;
        $string = '{"payload":{"foo":42,"bar":"baz"}}';

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage($string);

        $unserialize(Str::of($string));
    }

    public function testThrowWhenNoPayload()
    {
        $unserialize = new Unserialize;
        $string = '{"name":"foo"}';

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage($string);

        $unserialize(Str::of($string));
    }

    public function testThrowWhenPayloadNotAnArray()
    {
        $unserialize = new Unserialize;
        $string = '{"name":"foo","payload":"foo"}';

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage($string);

        $unserialize(Str::of($string));
    }
}
