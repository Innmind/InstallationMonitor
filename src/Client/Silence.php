<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor\Client;

use Innmind\InstallationMonitor\{
    Client,
    Event,
};
use Innmind\Immutable\{
    StreamInterface,
    Stream,
};

final class Silence implements Client
{
    private $client;

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

    /**
     * {@inheritdoc}
     */
    public function events(): StreamInterface
    {
        try {
            return $this->client->events();
        } catch (\RuntimeException $e) {
            return Stream::of(Event::class);
        }
    }
}
