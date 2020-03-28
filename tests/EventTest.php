<?php
declare(strict_types = 1);

namespace Tests\Innmind\InstallationMonitor;

use Innmind\InstallationMonitor\{
    Event,
    Event\Name,
    Exception\DomainException,
};
use Innmind\IPC\Message\Generic as Message;
use Innmind\MediaType\MediaType;
use Innmind\Immutable\{
    Map,
    Str,
};
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public function testInterface()
    {
        $event = new Event(
            $name = new Name('foo'),
            $payload = Map::of('string', 'scalar|array')
        );

        $this->assertSame($name, $event->name());
        $this->assertSame($payload, $event->payload());
    }

    public function testThrowWhenInvalidPayloadKey()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 2 must be of type Map<string, scalar|array>');

        new Event(new Name('foo'), Map::of('scalar', 'scalar|array'));
    }

    public function testThrowWhenInvalidPayloadValue()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 2 must be of type Map<string, scalar|array>');

        new Event(new Name('foo'), Map::of('string', 'scalar'));
    }

    /**
     * @dataProvider invalidTypes
     */
    public function testThrowWhenInvalidMediaType($type)
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('payload');

        Event::from(new Message(
            MediaType::of($type),
            Str::of('payload')
        ));
    }

    /**
     * @dataProvider invalidPayloads
     */
    public function testThrowWhenPayload($string)
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage($string);

        Event::from(new Message(
            MediaType::of('application/json'),
            Str::of($string)
        ));
    }

    public function testBuildFromMessage()
    {
        $event = Event::from(new Message(
            MediaType::of('application/json'),
            Str::of('{"name":"foo","payload":{"foo":42,"bar":"baz"}}')
        ));

        $this->assertInstanceOf(Event::class, $event);
        $this->assertSame('foo', (string) $event->name());
        $this->assertSame('string', (string) $event->payload()->keyType());
        $this->assertSame('scalar|array', (string) $event->payload()->valueType());
        $this->assertCount(2, $event->payload());
        $this->assertSame(42, $event->payload()->get('foo'));
        $this->assertSame('baz', $event->payload()->get('bar'));
    }

    public function testToMessage()
    {
        $event = new Event(
            new Name('foo'),
            Map::of('string', 'scalar|array')
                ('foo', 42)
                ('bar', 'baz')
        );

        $message = $event->toMessage();

        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame('application/json', $message->mediaType()->toString());
        $this->assertSame(
            '{"name":"foo","payload":{"foo":42,"bar":"baz"}}',
            $message->content()->toString(),
        );
    }

    public function invalidTypes(): array
    {
        return [
            ['text/plain'],
            ['application/octet-stream'],
        ];
    }

    public function invalidPayloads(): array
    {
        return [
            ['{"payload":{"foo":42,"bar":"baz"}}'],
            ['{"name":"foo"}'],
            ['{"name":"foo","payload":"foo"}'],
        ];
    }
}
