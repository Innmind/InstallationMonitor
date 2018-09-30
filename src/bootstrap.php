<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor;

use Innmind\Socket\Address\Unix as Address;
use Innmind\Server\Control\Server as ServerControl;
use Innmind\TimeContinuum\ElapsedPeriod;
use Innmind\CLI\Commands;

function bootstrap(): array
{
    $localServerAddress = new Address('/tmp/installation-monitor');

    return [
        'local_server_address' => $localServerAddress,
        'commands' => static function(Address $address, ServerControl $server): Commands {
            return new Commands(
                new Command\Oversee(
                    new Server\Local(
                        $address,
                        new ElapsedPeriod(1000) // 1 second
                    ),
                    $server
                )
            );
        },
        'client' => [
            'socket' => static function(Address $address = null) use ($localServerAddress): Client {
                return new Client\Socket($address ?? $localServerAddress);
            },
            'silence' => static function(Client $client): Client {
                return new Client\Silence($client);
            },
        ],
    ];
}
