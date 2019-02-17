<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor\Server;

use Innmind\InstallationMonitor\{
    Store,
    Event,
    IPC\Message\WaitingForEvents,
    IPC\Message\EndOfTransmission,
    Exception\DomainException,
};
use Innmind\IPC\{
    IPC,
    Message,
    Process\Name,
};

final class Local
{
    private $ipc;
    private $name;
    private $store;

    public function __construct(IPC $ipc, Name $name)
    {
        $this->ipc = $ipc;
        $this->name = $name;
        $this->store = new Store;
    }

    public function __invoke(): void
    {
        $dispatch = $this->ipc->listen($this->name);

        $dispatch(function(Message $message, Name $sender): void {
            if ((new WaitingForEvents)->equals($message)) {
                $this->sendEvents($sender);

                return;
            }

            try {
                $this->store->remember(Event::from($message));
            } catch (DomainException $e) {
                // never kill the server even when an invalid event is received
            }
        });
    }

    private function sendEvents(Name $sender): void
    {
        $this->ipc->wait($sender);
        $send = $this->ipc->get($sender)->send($this->name);
        $this->store->notify($send);
        $send(new EndOfTransmission);
    }
}
