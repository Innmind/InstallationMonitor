<?php
declare(strict_types = 1);

namespace Tests\Innmind\GuiltySparkMonitor;

use Innmind\GuiltySparkMonitor\{
    Store,
    IncomingConnection,
    Event,
    Event\Name,
};
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class StoreTest extends TestCase
{
    public function testBehaviour()
    {
        $store = new Store;

        $this->assertNull($store->remember($first = new Event(
            new Name('foo'),
            new Map('string', 'variable')
        )));
        $this->assertNull($store->remember($second = new Event(
            new Name('foo'),
            new Map('string', 'variable')
        )));
        $connection = $this->createMock(IncomingConnection::class);
        $connection
            ->expects($this->once())
            ->method('notify')
            ->with($first, $second);
        $this->assertNull($store->notify($connection));
    }
}
