<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor;

use Innmind\Immutable\Sequence;

interface Client
{
    public function send(Event ...$events): void;

    /**
     * @return Sequence<Event>
     */
    public function events(): Sequence;
}
