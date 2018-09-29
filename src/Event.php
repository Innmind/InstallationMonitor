<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor;

use Innmind\InstallationMonitor\Event\Name;
use Innmind\Immutable\MapInterface;

final class Event
{
    private $name;
    private $payload;

    public function __construct(Name $name, MapInterface $payload)
    {
        if (
            (string) $payload->keyType() !== 'string' ||
            (string) $payload->valueType() !== 'variable'
        ) {
            throw new \TypeError('Argument 2 must be of type MapInterface<string, variable>');
        }

        $this->name = $name;
        $this->payload = $payload;
    }

    public function name(): Name
    {
        return $this->name;
    }

    /**
     * @return MapInterface<string, variable>
     */
    public function payload(): MapInterface
    {
        return $this->payload;
    }
}
