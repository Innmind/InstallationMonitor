<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor\IPC\Message;

use Innmind\IPC\Message;
use Innmind\Filesystem\MediaType;
use Innmind\Immutable\Str;

final class EndOfTransmission implements Message
{
    public function mediaType(): MediaType
    {
        return MediaType\MediaType::fromString('text/plain');
    }

    public function content(): Str
    {
        return Str::of('end-of-transmission');
    }

    public function equals(Message $message): bool
    {
        return (string) $this->mediaType() === (string) $message->mediaType() &&
            (string) $this->content() === (string) $message->content();
    }
}
