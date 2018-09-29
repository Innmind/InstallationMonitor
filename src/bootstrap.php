<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor;

use Innmind\Socket\Address\Unix as Address;

function bootstrap(): array
{
    return [
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
