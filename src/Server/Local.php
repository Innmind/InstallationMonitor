<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor\Server;

use Innmind\InstallationMonitor\{
    Server\Local\Dispatch,
    Store,
};
use Innmind\OperatingSystem\Sockets;
use Innmind\Socket\{
    Address\Unix,
    Loop,
    Loop\Strategy,
    Event\ConnectionReceived,
    Event\ConnectionClosed,
    Event\DataReceived,
};
use Innmind\EventBus\EventBus;
use Innmind\TimeContinuum\ElapsedPeriod;
use Innmind\Immutable\{
    SetInterface,
    Set,
    Map,
};

final class Local
{
    private $sockets;
    private $address;
    private $timeout;
    private $strategy;
    private $dispatch;

    public function __construct(
        Sockets $sockets,
        Unix $address,
        ElapsedPeriod $timeout,
        Strategy $strategy = null
    ) {
        $this->sockets = $sockets;
        $this->address = $address;
        $this->timeout = $timeout;
        $this->strategy = $strategy;
        $this->dispatch = new Dispatch(new Store);
    }

    public function __invoke(): void
    {
        $server = $this->sockets->takeOver($this->address);
        $loop = new Loop(
            new EventBus\Map(
                Map::of('string', 'callable')
                    (ConnectionReceived::class, $this->dispatch)
                    (DataReceived::class, $this->dispatch)
                    (ConnectionClosed::class, $this->dispatch)
            ),
            $this->timeout,
            $this->strategy
        );
        $loop($server);
    }
}
