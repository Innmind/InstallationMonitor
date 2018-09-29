<?php
declare(strict_types = 1);

namespace Tests\Innmind\InstallationMonitor\Server;

use Innmind\InstallationMonitor\Server\Local;
use Innmind\Socket\{
    Address\Unix,
    Loop\Strategy,
};
use Innmind\TimeContinuum\ElapsedPeriod;
use PHPUnit\Framework\TestCase;

class LocalTest extends TestCase
{
    public function testInvokation()
    {
        $listen = new Local(
            new Unix('/tmp/local-server'),
            new ElapsedPeriod(1000), // 1 second
            new class implements Strategy {
                public function __invoke(): bool
                {
                    return false;
                }
            }
        );

        $start = microtime(true);
        $this->assertNull($listen());
        $end = microtime(true);

        $this->assertEquals(1, $end - $start, '', 0.015);
    }
}
