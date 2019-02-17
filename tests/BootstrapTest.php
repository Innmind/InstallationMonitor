<?php
declare(strict_types = 1);

namespace Tests\Innmind\InstallationMonitor;

use function Innmind\InstallationMonitor\bootstrap;
use Innmind\InstallationMonitor\{
    Client\IPC,
    Client\Silence,
    Client,
};
use Innmind\OperatingSystem\OperatingSystem;
use Innmind\CLI\Commands;
use PHPUnit\Framework\TestCase;

class BootstrapTest extends TestCase
{
    public function testBootstrap()
    {
        $services = bootstrap($this->createMock(OperatingSystem::class));

        $this->assertCount(2, $services['client']);
        $this->assertIsCallable($services['client']['ipc']);
        $this->assertInstanceOf(IPC::class, $services['client']['ipc']());
        $this->assertIsCallable($services['client']['silence']);
        $this->assertInstanceOf(Silence::class, $services['client']['silence'](
            $this->createMock(Client::class)
        ));
        $this->assertIsCallable($services['commands']);
        $this->assertInstanceOf(
            Commands::class,
            $services['commands']()
        );
    }
}
