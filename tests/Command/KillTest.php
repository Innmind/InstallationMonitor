<?php
declare(strict_types = 1);

namespace Tests\Innmind\InstallationMonitor\Command;

use Innmind\InstallationMonitor\Command\Kill;
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};
use Innmind\Server\Status\{
    Server as ServerStatus,
};
use Innmind\Server\Control\{
    Server as ServerControl,
};
use Innmind\TimeContinuum\PointInTime;
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class KillTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Command::class,
            new Kill(
                $this->createMock(ServerStatus::class),
                $this->createMock(ServerControl::class)
            )
        );
    }

    public function testUsage()
    {
        $usage = <<<USAGE
kill

Will kill the monitor (if any running) overseeing the installation
USAGE;

        $this->assertSame(
            $usage,
            (new Kill(
                $this->createMock(ServerStatus::class),
                $this->createMock(ServerControl::class)
            ))->toString()
        );
    }

    public function testInvokation()
    {
        $kill = new Kill(
            $status = $this->createMock(ServerStatus::class),
            $control = $this->createMock(ServerControl::class)
        );
        $status
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(ServerStatus\Processes::class));
        $processes
            ->expects($this->once())
            ->method('all')
            ->willReturn(
                Map::of('int', ServerStatus\Process::class)
                    (
                        2,
                        new ServerStatus\Process(
                            new ServerStatus\Process\Pid(2),
                            new ServerStatus\Process\User('root'),
                            new ServerStatus\Cpu\Percentage(0.0),
                            new ServerStatus\Process\Memory(0.0),
                            $this->createMock(PointInTime::class),
                            new ServerStatus\Process\Command('php /root/.composer/vendor/bin/installation-monitor oversee')
                        )
                    )
                    (
                        3,
                        new ServerStatus\Process(
                            new ServerStatus\Process\Pid(3),
                            new ServerStatus\Process\User('root'),
                            new ServerStatus\Cpu\Percentage(0.0),
                            new ServerStatus\Process\Memory(0.0),
                            $this->createMock(PointInTime::class),
                            new ServerStatus\Process\Command('grep installation-monitor')
                        )
                    )
                    (
                        4,
                        new ServerStatus\Process(
                            new ServerStatus\Process\Pid(4),
                            new ServerStatus\Process\User('root'),
                            new ServerStatus\Cpu\Percentage(0.0),
                            new ServerStatus\Process\Memory(0.0),
                            $this->createMock(PointInTime::class),
                            new ServerStatus\Process\Command('php /root/.composer/vendor/bin/installation-monitor oversee')
                        )
                    )
            );
        $control
            ->expects($this->exactly(2))
            ->method('processes')
            ->willReturn($processes = $this->createMock(ServerControl\Processes::class));
        $processes
            ->expects($this->exactly(2))
            ->method('kill')
            ->withConsecutive(
                [
                    new ServerControl\Process\Pid(2),
                    ServerControl\Signal::terminate()
                ],
                [
                    new ServerControl\Process\Pid(4),
                    ServerControl\Signal::terminate()
                ],
            );

        $this->assertNull($kill(
            $this->createMock(Environment::class),
            new Arguments,
            new Options
        ));
    }
}
