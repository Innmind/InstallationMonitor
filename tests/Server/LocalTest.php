<?php
declare(strict_types = 1);

namespace Tests\Innmind\InstallationMonitor\Server;

use Innmind\InstallationMonitor\Server\Local;
use Innmind\Socket\{
    Address\Unix,
    Loop\Strategy,
    Server\Unix as Socket,
};
use Innmind\TimeContinuum\ElapsedPeriod;
use Innmind\OperatingSystem\Sockets;
use PHPUnit\Framework\TestCase;

class LocalTest extends TestCase
{
    public function testInvokation()
    {
        $listen = new Local(
            $sockets = $this->createMock(Sockets::class),
            $address = new Unix('/tmp/local-server'),
            new ElapsedPeriod(1000), // 1 second
            new class implements Strategy {
                public function __invoke(): bool
                {
                    return false;
                }
            }
        );
        $sockets
            ->expects($this->once())
            ->method('takeOver')
            ->with($address)
            ->willReturn(Socket::recoverable($address));

        $start = microtime(true);
        $this->assertNull($listen());
        $end = microtime(true);

        $this->assertEquals(1, $end - $start, '', 0.02);
    }
}
