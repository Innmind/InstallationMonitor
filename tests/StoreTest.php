<?php
declare(strict_types = 1);

namespace Tests\Innmind\InstallationMonitor;

use Innmind\InstallationMonitor\{
    Store,
    Event,
    Event\Name,
};
use Innmind\IPC\{
    Message\Generic as Message,
    Client,
};
use Innmind\Filesystem\MediaType\MediaType;
use Innmind\Immutable\{
    Map,
    Str,
};
use PHPUnit\Framework\TestCase;

class StoreTest extends TestCase
{
    public function testBehaviour()
    {
        $store = new Store;

        $this->assertNull($store->remember(new Event(
            new Name('foo'),
            new Map('string', 'variable')
        )));
        $this->assertNull($store->remember(new Event(
            new Name('bar'),
            new Map('string', 'variable')
        )));
        $client = $this->createMock(Client::class);
        $client
            ->expects($this->once())
            ->method('send')
            ->with(
                new Message(
                    MediaType::fromString('application/json'),
                    Str::of('{"name":"foo","payload":[]}')
                ),
                new Message(
                    MediaType::fromString('application/json'),
                    Str::of('{"name":"bar","payload":[]}')
                )
            );
        $this->assertNull($store->notify($client));
    }
}
