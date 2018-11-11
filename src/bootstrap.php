<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor;

use Innmind\Socket\Address\Unix as Address;
use Innmind\OperatingSystem\{
    OperatingSystem,
    Sockets,
};
use Innmind\TimeContinuum\ElapsedPeriod;
use Innmind\CLI\Commands;

function bootstrap(): array
{
    $localServerAddress = new Address('/tmp/installation-monitor');

    return [
        'local_server_address' => $localServerAddress,
        'commands' => static function(Address $address, OperatingSystem $os): Commands {
            return new Commands(
                new Command\Oversee(
                    new Server\Local(
                        $os->sockets(),
                        $address,
                        new ElapsedPeriod(1000) // 1 second
                    ),
                    $os->control()
                ),
                new Command\Kill($os->status(), $os->control())
            );
        },
        'client' => [
            'socket' => static function(Sockets $sockets, Address $address = null) use ($localServerAddress): Client {
                return new Client\Socket($sockets, $address ?? $localServerAddress);
            },
            'silence' => static function(Client $client): Client {
                return new Client\Silence($client);
            },
        ],
    ];
}
