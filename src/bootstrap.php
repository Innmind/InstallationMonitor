<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor;

use Innmind\OperatingSystem\OperatingSystem;
use Innmind\CLI\Commands;
use Innmind\IPC\Process\Name;
use function Innmind\IPC\bootstrap as ipc;

function bootstrap(OperatingSystem $os): array
{
    $localServerName = new Name('installation-monitor');
    $ipc = ipc($os);

    return [
        'local_server_name' => $localServerName,
        'commands' => static function() use ($ipc, $localServerName, $os): Commands {
            return new Commands(
                new Command\Oversee(
                    new Server\Local(
                        $ipc,
                        $localServerName,
                    ),
                    $os->control(),
                ),
                new Command\Kill($os->status(), $os->control()),
            );
        },
        'client' => [
            'ipc' => static function() use ($ipc, $localServerName): Client {
                return new Client\IPC($ipc, $localServerName);
            },
            'silence' => static function(Client $client): Client {
                return new Client\Silence($client);
            },
        ],
    ];
}
