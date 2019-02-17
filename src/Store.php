<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor;

use Innmind\IPC\{
    Sender,
    Message,
};
use Innmind\Immutable\{
    StreamInterface,
    Stream,
};

final class Store
{
    private $events;

    public function __construct()
    {
        $this->events = Stream::of(Event::class);
    }

    public function remember(Event $event): void
    {
        $this->events = $this->events->add($event);
    }

    public function notify(Sender $send): void
    {
        $send(
            ...$this
                ->events
                ->reduce(
                    Stream::of(Message::class),
                    static function(StreamInterface $messages, Event $event): StreamInterface {
                        return $messages->add($event->toMessage());
                    }
                )
        );
    }
}
