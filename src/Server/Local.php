<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor\Server;

use Innmind\InstallationMonitor\{
    Store,
    Event,
    IPC\Message\WaitingForEvents,
    Exception\DomainException,
};
use Innmind\IPC\{
    IPC,
    Message,
    Client,
    Process\Name,
};

final class Local
{
    private IPC $ipc;
    private Name $name;
    private Store $store;

    public function __construct(IPC $ipc, Name $name)
    {
        $this->ipc = $ipc;
        $this->name = $name;
        $this->store = new Store;
    }

    public function __invoke(): void
    {
        $dispatch = $this->ipc->listen($this->name);

        $dispatch(function(Message $message, Client $client): void {
            if ($message->equals(new WaitingForEvents)) {
                $this->sendEvents($client);

                return;
            }

            try {
                $this->store->remember(Event::from($message));
            } catch (DomainException $e) {
                // never kill the server even when an invalid event is received
            }
        });
    }

    private function sendEvents(Client $client): void
    {
        $this->store->notify($client);
        $client->close();
    }
}
