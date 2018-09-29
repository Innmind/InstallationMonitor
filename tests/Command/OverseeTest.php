<?php
declare(strict_types = 1);

namespace Tests\Innmind\InstallationMonitor\Command;

use Innmind\InstallationMonitor\{
    Command\Oversee,
    Server\Local,
};
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};
use Innmind\Socket\{
    Address\Unix as Address,
    Loop\Strategy,
};
use Innmind\TimeContinuum\ElapsedPeriod;
use Innmind\Server\Control\{
    Server,
    Server\Processes,
};
use Innmind\Url\Path;
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class OverseeTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Command::class,
            new Oversee(
                new Local(
                    new Address('/tmp/foo'),
                    new ElapsedPeriod(1000)
                ),
                $this->createMock(Server::class)
            )
        );
    }

    public function testUsage()
    {
        $usage = <<<USAGE
oversee -d|--daemon

Start a socket to collect events emitted locally by other apps

The "d" option will run this command in the background
USAGE;

        $this->assertSame(
            $usage,
            (string) new Oversee(
                new Local(
                    new Address('/tmp/foo'),
                    new ElapsedPeriod(1000)
                ),
                $this->createMock(Server::class)
            )
        );
    }

    public function testInvokation()
    {
        $oversee = new Oversee(
            new Local(
                new Address('/tmp/foo'),
                new ElapsedPeriod(1000),
                new class implements Strategy {
                    public function __invoke(): bool
                    {
                        return false;
                    }
                }
            ),
            $server = $this->createMock(Server::class)
        );
        $server
            ->expects($this->never())
            ->method('processes');

        $this->assertNull($oversee(
            $this->createMock(Environment::class),
            new Arguments,
            new Options
        ));
    }

    public function testDaemonize()
    {
        $oversee = new Oversee(
            new Local(
                new Address('/tmp/foo'),
                new ElapsedPeriod(1000),
                new class implements Strategy {
                    public function __invoke(): bool
                    {
                        return false;
                    }
                }
            ),
            $server = $this->createMock(Server::class)
        );
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $processes
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return (string) $command === "installation-monitor 'oversee'" &&
                    $command->toBeRunInBackground() &&
                    $command->workingDirectory() === '/tmp';
            }));

        $env = $this->createMock(Environment::class);
        $env
            ->expects($this->once())
            ->method('workingDirectory')
            ->willReturn(new Path('/tmp'));

        $this->assertNull($oversee(
            $env,
            new Arguments,
            new Options(
                (new Map('string', 'mixed'))
                    ->put('daemon', true)
            )
        ));
    }
}
