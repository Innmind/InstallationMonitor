<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor\Client;

use Innmind\InstallationMonitor\{
    Client,
    Event,
    Events,
};
use Innmind\Socket\Address\Unix;
use Innmind\Stream\Select;
use Innmind\TimeContinuum\ElapsedPeriod;
use Innmind\OperatingSystem\Sockets;
use Innmind\Immutable\{
    StreamInterface,
    Stream,
    Str,
};

final class Socket implements Client
{
    private $sockets;
    private $address;

    public function __construct(Sockets $sockets, Unix $address)
    {
        $this->sockets = $sockets;
        $this->address = $address;
    }

    public function send(Event ...$events): void
    {
        $events = new Events(...$events);

        if ($events->count() === 0) {
            return;
        }

        $socket = $this->sockets->connectTo($this->address);

        $socket->write($events->toString());

        $socket->close();
    }

    /**
     * {@inheritdoc}
     */
    public function events(): StreamInterface
    {
        $socket = $this->sockets->connectTo($this->address);

        $select = new Select(new ElapsedPeriod(1000)); // 1 second timeout
        $select = $select->forRead($socket);

        $events = Str::of('', 'ASCII');
        $timedOutIterations = 0;

        do {
            $sockets = $select();

            if ($sockets->get('read')->contains($socket)) {
                $events = $events->append($socket->read());
            } else {
                ++$timedOutIterations;
            }
        } while ($timedOutIterations < 2);

        $socket->close();

        return Stream::of(Event::class, ...Events::from($events));
    }
}
