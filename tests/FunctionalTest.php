<?php
declare(strict_types = 1);

namespace Tests\Innmind\InstallationMonitor;

use function Innmind\InstallationMonitor\bootstrap;
use Innmind\InstallationMonitor\Event;
use Innmind\OperatingSystem\Factory;
use Innmind\Server\Control\Server\{
    Command,
    Signal,
};
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class FunctionalTest extends TestCase
{
    public function testBehaviour()
    {
        $os = Factory::build();
        $server = $os
            ->control()
            ->processes()
            ->execute(
                Command::foreground('./installation-monitor')
                    ->withArgument('oversee')
            );
        sleep(1);

        $client = bootstrap($os)['client']['ipc']();
        $event = new Event(
            new Event\Name('test'),
            Map::of('string', 'variable')
                ('foo', 42)
        );
        $client->send($event);
        $events = $client->events();

        $this->assertCount(1, $events);
        $this->assertEquals($event, $events->current());

        $os->control()->processes()->kill(
            $server->pid(),
            Signal::terminate()
        );
    }
}
