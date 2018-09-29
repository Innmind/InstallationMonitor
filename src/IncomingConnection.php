<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor;

interface IncomingConnection
{
    public function notify(Event ...$events): void;
}
