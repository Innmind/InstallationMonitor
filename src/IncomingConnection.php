<?php
declare(strict_types = 1);

namespace Innmind\GuiltySparkMonitor;

interface IncomingConnection
{
    public function notify(Event ...$events): void;
}
