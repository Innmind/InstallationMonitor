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
use function Innmind\Immutable\assertMap;

final class Event
{
    private Name $name;
    /** @var Map<string, scalar|array> */
    private Map $payload;

    /**
     * @param Map<string, scalar|array> $payload
     */
    public function __construct(Name $name, Map $payload)
    {
        assertMap('string', 'scalar|array', $payload, 2);

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

        /** @var array{name: string, payload: array<string, scalar|array>} */
        $data = Json::decode($message->content()->toString());

        /** @psalm-suppress DocblockTypeContradiction */
        if (
            !isset($data['name']) ||
            !isset($data['payload']) ||
            !\is_array($data['payload'])
        ) {
            throw new DomainException($message->content()->toString());
        }

        /** @var Map<string, scalar|array> */
        $payload = Map::of('string', 'scalar|array');

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
     * @return Map<string, scalar|array>
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
                },
            ),
        ]));

        return new Message\Generic(
            MediaType::of('application/json'),
            $content,
        );
    }
}
