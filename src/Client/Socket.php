<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor\Client;

use Innmind\InstallationMonitor\{
    Client,
    Event,
    Serialize,
    Unserialize,
};
use Innmind\Socket\{
    Address\Unix as Address,
    Client\Unix,
};
use Innmind\Stream\Select;
use Innmind\TimeContinuum\ElapsedPeriod;
use Innmind\Immutable\{
    StreamInterface,
    Stream,
};

final class Socket implements Client
{
    private $address;
    private $serialize;
    private $unserialize;

    public function __construct(Address $address)
    {
        $this->address = $address;
        $this->serialize = new Serialize;
        $this->unserialize = new Unserialize;
    }

    public function send(Event ...$events): void
    {
        $socket = new Unix($this->address);

        foreach ($events as $event) {
            $socket->write(
                ($this->serialize)($event)
            );
        }

        $socket->close();
    }

    /**
     * {@inheritdoc}
     */
    public function events(): StreamInterface
    {
        $socket = new Unix($this->address);

        $select = new Select(new ElapsedPeriod(1000)); // 1 second timeout
        $select = $select->forRead($socket);

        $events = Stream::of(Event::class);
        $timedOutIterations = 0;

        do {
            $sockets = $select();

            if ($sockets->get('read')->contains($socket)) {
                $events = $events->add(
                    ($this->unserialize)($socket->read())
                );
            } else {
                ++$timedOutIterations;
            }
        } while($timedOutIterations < 2);

        $socket->close();

        return $events;
    }
}
