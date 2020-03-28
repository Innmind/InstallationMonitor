<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor\Command;

use Innmind\InstallationMonitor\Server\Local;
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};
use Innmind\Server\Control\{
    Server,
    Server\Command as ServerCommand,
};

final class Oversee implements Command
{
    private Local $listen;
    private Server $server;

    public function __construct(Local $listen, Server $server)
    {
        $this->listen = $listen;
        $this->server = $server;
    }

    public function __invoke(Environment $env, Arguments $arguments, Options $options): void
    {
        if ($options->contains('daemon')) {
            $this
                ->server
                ->processes()
                ->execute(
                    ServerCommand::background('installation-monitor')
                        ->withArgument('oversee')
                        ->withWorkingDirectory((string) $env->workingDirectory())
                );

            return;
        }

        ($this->listen)();
    }

    public function __toString(): string
    {
        return <<<USAGE
oversee -d|--daemon

Start a socket to collect events emitted locally by other apps

The "d" option will run this command in the background
USAGE;
    }
}
