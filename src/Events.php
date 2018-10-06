<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor;

use Innmind\Immutable\{
    SequenceInterface,
    Sequence,
    Str,
};

final class Events implements \Iterator, \Countable
{
    private $events;
    private $serialize;

    public function __construct(Event ...$events)
    {
        $this->events = Sequence::of(...$events);
        $this->serialize = new Serialize;
    }

    public static function from(Str $string): self
    {
        $unserialize = new Unserialize;

        return new self(
            ...$string
                ->toEncoding('ASCII')
                ->split(self::boundary())
                ->reduce(
                    new Sequence,
                    static function(SequenceInterface $events, Str $event) use ($unserialize): SequenceInterface {
                        return $events->add($unserialize($event));
                    }
                )
        );
    }

    public function toString(): Str
    {
        return $this
            ->events
            ->map(function(Event $event): Str {
                return ($this->serialize)($event);
            })
            ->join(self::boundary())
            ->toEncoding('ASCII');
    }

    public function count(): int
    {
        return $this->events->size();
    }

    public function current(): Event
    {
        return $this->events->current();
    }

    public function key(): int
    {
        return $this->events->key();
    }

    public function next(): void
    {
        $this->events->next();
    }

    public function rewind(): void
    {
        $this->events->rewind();
    }

    public function valid(): bool
    {
        return $this->events->valid();
    }

    private static function boundary(): string
    {
        return 'ø';
    }
}
