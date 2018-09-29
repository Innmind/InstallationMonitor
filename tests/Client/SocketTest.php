<?php
declare(strict_types = 1);

namespace Tests\Innmind\InstallationMonitor\Client;

use Innmind\InstallationMonitor\{
    Client\Socket,
    Client,
    Event,
};
use Innmind\Socket\{
    Address\Unix as Address,
    Server\Unix as Server,
};
use Innmind\Immutable\{
    Map,
    StreamInterface,
};
use PHPUnit\Framework\TestCase;

class SocketTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Client::class, new Socket(new Address('/tmp/foo')));
    }

    public function testSend()
    {
        $address = new Address('/tmp/foo');
        $server = Server::recoverable($address);

        $client = new Socket($address);

        $this->assertNull($client->send(new Event(
            new Event\Name('bar'),
            new Map('string', 'variable')
        )));

        $connection = $server->accept();
        $this->assertSame(
            '{"name":"bar","payload":[]}',
            (string) $connection->read()
        );
    }

    public function testEvents()
    {
        $address = new Address('/tmp/foo');
        $server = Server::recoverable($address);

        $client = new Socket($address);

        $start = microtime(true);
        $events = $client->events();
        $end = microtime(true);

        $this->assertInstanceOf(StreamInterface::class, $events);
        $this->assertSame(Event::class, (string) $events->type());
        $this->assertCount(0, $events); // empty as from here we can't push events to the server
        $this->assertEquals(2, $end - $start, '', 0.015);
    }
}
