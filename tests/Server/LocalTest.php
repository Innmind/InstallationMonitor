<?php
declare(strict_types = 1);

namespace Tests\Innmind\InstallationMonitor\Server;

use Innmind\InstallationMonitor\{
    Server\Local,
    IPC\Message\WaitingForEvents,
    IPC\Message\EndOfTransmission,
};
use Innmind\IPC\{
    IPC,
    Process,
    Process\Name,
    Message\Generic as Message,
    Receiver,
    Sender,
};
use Innmind\Filesystem\MediaType\MediaType;
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
        $sender = new Name('sender');
        $event = new Message(
            MediaType::fromString('application/json'),
            Str::of('{"name":"foo","payload":[]}')
        );
        $invalidEvent = new Message(
            MediaType::fromString('text/plain'),
            Str::of('{"name":"foo","payload":[]}')
        );
        $waitingForEvents = new WaitingForEvents;
        $ipc
            ->expects($this->at(0))
            ->method('listen')
            ->with($name)
            ->willReturn($receiver = $this->createMock(Receiver::class));
        $receiver
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->callback(static function($listen) use ($event, $invalidEvent, $waitingForEvents, $sender): bool {
                $listen($event, new Name('unknown-source'));
                $listen($invalidEvent, new Name('unknown-source'));
                $listen($waitingForEvents, $sender);

                return true;
            }));
        $ipc
            ->expects($this->at(1))
            ->method('wait')
            ->with($sender);
        $ipc
            ->expects($this->at(2))
            ->method('get')
            ->with($sender)
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('send')
            ->with($name)
            ->willReturn($sender = $this->createMock(Sender::class));
        $sender
            ->expects($this->at(0))
            ->method('__invoke')
            ->with($event);
        $sender
            ->expects($this->at(1))
            ->method('__invoke')
            ->with(new EndOfTransmission);

        $this->assertNull($listen());
    }
}
