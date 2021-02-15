<?php
declare(strict_types = 1);

namespace Tests\Innmind\InstallationMonitor\Server;

use Innmind\InstallationMonitor\{
    Server\Local,
    IPC\Message\WaitingForEvents,
};
use Innmind\IPC\{
    IPC,
    Process,
    Process\Name,
    Message\Generic as Message,
    Server,
    Client,
};
use Innmind\MediaType\MediaType;
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;

class LocalTest extends TestCase
{
    public function testInvokation()
    {
        $listen = new Local(
            $ipc = $this->createMock(IPC::class),
            $name = new Name('local-server')
        );
        $sender = $this->createMock(Client::class);
        $event = new Message(
            MediaType::of('application/json'),
            Str::of('{"name":"foo","payload":[]}')
        );
        $invalidEvent = new Message(
            MediaType::of('text/plain'),
            Str::of('{"name":"foo","payload":[]}')
        );
        $waitingForEvents = new WaitingForEvents;
        $ipc
            ->expects($this->once())
            ->method('listen')
            ->with($name)
            ->willReturn($server = $this->createMock(Server::class));
        $server
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(function($listen) use ($event, $invalidEvent, $waitingForEvents, $sender): bool {
                $listen($event, $this->createMock(Client::class));
                $listen($invalidEvent, $this->createMock(Client::class));
                $listen($waitingForEvents, $sender);

                return true;
            }));
        $sender
            ->expects($this->once())
            ->method('send')
            ->with($event);
        $sender
            ->expects($this->once())
            ->method('close');

        $this->assertNull($listen());
    }
}
