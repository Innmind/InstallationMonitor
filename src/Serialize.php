<?php
declare(strict_types = 1);

namespace Innmind\GuiltySparkMonitor;

use Innmind\Json\Json;
use Innmind\Immutable\Str;

final class Serialize
{
    public function __invoke(Event $event): Str
    {
        return new Str(Json::encode([
            'name' => (string) $event->name(),
            'payload' => $event->payload()->reduce(
                [],
                static function(array $carry, string $key, $value): array {
                    $carry[$key] = $value;

                    return $carry;
                }
            ),
        ]));
    }
}
