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
use Innmind\MediaType\MediaType;
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
            Map::of('string', 'variable')
        )));
        $this->assertNull($store->remember(new Event(
            new Name('bar'),
            Map::of('string', 'variable')
        )));
        $client = $this->createMock(Client::class);
        $client
            ->expects($this->at(0))
            ->method('send')
            ->with(new Message(
                MediaType::of('application/json'),
                Str::of('{"name":"foo","payload":[]}')
            ));
        $client
            ->expects($this->at(1))
            ->method('send')
            ->with(new Message(
                MediaType::of('application/json'),
                Str::of('{"name":"bar","payload":[]}')
            ));
        $this->assertNull($store->notify($client));
    }
}
