<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor\Server;

use Innmind\InstallationMonitor\{
    Server\Local\Dispatch,
    Store,
};
use Innmind\Socket\{
    Address\Unix as Address,
    Server\Unix,
    Loop,
    Loop\Strategy,
    Event\ConnectionReceived,
    Event\ConnectionClosed,
    Event\DataReceived,
};
use Innmind\EventBus\EventBus;
use Innmind\Immutable\{
    SetInterface,
    Set,
    Map,
};
use Innmind\TimeContinuum\ElapsedPeriod;

final class Local
{
    private $address;
    private $timeout;
    private $strategy;
    private $dispatch;

    public function __construct(
        Address $address,
        ElapsedPeriod $timeout,
        Strategy $strategy = null
    ) {
        $this->address = $address;
        $this->timeout = $timeout;
        $this->strategy = $strategy;
        $this->dispatch = new Dispatch(new Store);
    }

    public function __invoke(): void
    {
        $listeners = Set::of('callable', $this->dispatch);

        $server = Unix::recoverable($this->address);
        $loop = new Loop(
            new EventBus(
                (new Map('string', SetInterface::class))
                    ->put(ConnectionReceived::class, $listeners)
                    ->put(DataReceived::class, $listeners)
                    ->put(ConnectionClosed::class, $listeners)
            ),
            $this->timeout,
            $this->strategy
        );
        $loop($server);
    }
}
