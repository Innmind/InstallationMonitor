<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor;

use Innmind\InstallationMonitor\{
    Event\Name,
    Exception\DomainException,
};
use Innmind\IPC\Message;
use Innmind\MediaType\MediaType;
use Innmind\Json\Json;
use Innmind\Immutable\{
    Map,
    Str,
};

final class Event
{
    private Name $name;
    private Map $payload;

    public function __construct(Name $name, Map $payload)
    {
        if (
            (string) $payload->keyType() !== 'string' ||
            (string) $payload->valueType() !== 'variable'
        ) {
            throw new \TypeError('Argument 2 must be of type Map<string, variable>');
        }

        $this->name = $name;
        $this->payload = $payload;
    }

    public static function from(Message $message): self
    {
        if (
            $message->mediaType()->topLevel() !== 'application' ||
            $message->mediaType()->subType() !== 'json'
        ) {
            throw new DomainException($message->content()->toString());
        }

        $data = Json::decode($message->content()->toString());

        if (
            !isset($data['name']) ||
            !isset($data['payload']) ||
            !\is_array($data['payload'])
        ) {
            throw new DomainException($message->content()->toString());
        }

        $payload = Map::of('string', 'variable');

        foreach ($data['payload'] as $key => $value) {
            $payload = ($payload)($key, $value);
        }

        return new self(
            new Name($data['name']),
            $payload,
        );
    }

    public function name(): Name
    {
        return $this->name;
    }

    /**
     * @return Map<string, variable>
     */
    public function payload(): Map
    {
        return $this->payload;
    }

    public function toMessage(): Message
    {
        $content = Str::of(Json::encode([
            'name' => (string) $this->name(),
            'payload' => $this->payload()->reduce(
                [],
                static function(array $carry, string $key, $value): array {
                    $carry[$key] = $value;

                    return $carry;
                }
            ),
        ]));

        return new Message\Generic(
            MediaType::of('application/json'),
            $content
        );
    }
}
