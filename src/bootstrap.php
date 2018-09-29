<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor;

use Innmind\Socket\Address\Unix as Address;
use Innmind\Server\Control\Server as ServerControl;
use Innmind\TimeContinuum\ElapsedPeriod;
use Innmind\CLI\Commands;

function bootstrap(): array
{
    return [
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
            'socket' => static function(Address $address): Client {
                return new Client\Socket($address);
            },
            'silence' => static function(Client $client): Client {
                return new Client\Silence($client);
            },
        ],
    ];
}
