<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor\Client;

use Innmind\InstallationMonitor\{
    Client,
    Event,
};
use Innmind\Immutable\Sequence;

final class Silence implements Client
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function send(Event ...$events): void
    {
        try {
            $this->client->send(...$events);
        } catch (\RuntimeException $e) {
            // do nothing
        }
    }

    public function events(): Sequence
    {
        try {
            return $this->client->events();
        } catch (\RuntimeException $e) {
            return Sequence::of(Event::class);
        }
    }
}
