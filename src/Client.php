<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor;

use Innmind\Immutable\StreamInterface;

interface Client
{
    public function send(Event ...$events): void;

    /**
     * @return StreamInterface<Event>
     */
    public function events(): StreamInterface;
}
