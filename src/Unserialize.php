<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor;

use Innmind\InstallationMonitor\{
    Event\Name,
    Exception\DomainException,
};
use Innmind\Json\Json;
use Innmind\Immutable\{
    Map,
    Str,
};

final class Unserialize
{
    public function __invoke(Str $string): Event
    {
        $data = Json::decode((string) $string);

        if (
            !isset($data['name']) ||
            !isset($data['payload']) ||
            !is_array($data['payload'])
        ) {
            throw new DomainException((string) $string);
        }

        return new Event(
            new Name($data['name']),
            Map::of(
                'string',
                'variable',
                array_keys($data['payload']),
                array_values($data['payload'])
            )
        );
    }
}
