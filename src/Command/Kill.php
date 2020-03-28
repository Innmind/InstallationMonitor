<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor\Command;

use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};
use Innmind\Server\Status\{
    Server as ServerStatus,
    Server\Process,
};
use Innmind\Server\Control\{
    Server as ServerControl,
    Server\Process\Pid,
    Server\Signal,
};
use Innmind\Immutable\Str;

final class Kill implements Command
{
    private ServerStatus $status;
    private ServerControl $control;

    public function __construct(ServerStatus $status, ServerControl $control)
    {
        $this->status = $status;
        $this->control = $control;
    }

    public function __invoke(Environment $env, Arguments $arguments, Options $options): void
    {
        $this
            ->status
            ->processes()
            ->all()
            ->filter(static function(int $pid, Process $process): bool {
                return Str::of($process->command()->toString())->matches(
                    '~installation-monitor oversee~',
                );
            })
            ->foreach(function(int $pid, Process $process): void {
                $this
                    ->control
                    ->processes()
                    ->kill(
                        new Pid($process->pid()->toInt()),
                        Signal::terminate(),
                    );
            });
    }

    public function toString(): string
    {
        return <<<USAGE
kill

Will kill the monitor (if any running) overseeing the installation
USAGE;
    }
}
