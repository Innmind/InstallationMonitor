<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor;

use Innmind\InstallationMonitor\{
    Event\Name,
    Exception\DomainException,
};
use Innmind\IPC\Message;
use Innmind\Filesystem\MediaType\MediaType;
use Innmind\Json\Json;
use Innmind\Immutable\{
    MapInterface,
    Map,
    Str,
};

final class Event
{
    private $name;
    private $payload;

    public function __construct(Name $name, MapInterface $payload)
    {
        if (
            (string) $payload->keyType() !== 'string' ||
            (string) $payload->valueType() !== 'variable'
        ) {
            throw new \TypeError('Argument 2 must be of type MapInterface<string, variable>');
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
            throw new DomainException((string) $message->content());
        }

        $data = Json::decode((string) $message->content());

        if (
            !isset($data['name']) ||
            !isset($data['payload']) ||
            !\is_array($data['payload'])
        ) {
            throw new DomainException((string) $message->content());
        }

        return new self(
            new Name($data['name']),
            Map::of(
                'string',
                'variable',
                \array_keys($data['payload']),
                \array_values($data['payload'])
            )
        );
    }

    public function name(): Name
    {
        return $this->name;
    }

    /**
     * @return MapInterface<string, variable>
     */
    public function payload(): MapInterface
    {
        return $this->payload;
    }

    public function toMessage(): Message
    {
        $content = new Str(Json::encode([
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
            MediaType::fromString('application/json'),
            $content
        );
    }
}
