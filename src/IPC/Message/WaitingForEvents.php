<?php
declare(strict_types = 1);

namespace Innmind\InstallationMonitor\IPC\Message;

use Innmind\IPC\Message;
use Innmind\MediaType\MediaType;
use Innmind\Immutable\Str;

final class WaitingForEvents implements Message
{
    public function mediaType(): MediaType
    {
        return MediaType::of('text/plain');
    }

    public function content(): Str
    {
        return Str::of('waiting-for-events');
    }

    public function equals(Message $message): bool
    {
        return $this->mediaType()->toString() === $message->mediaType()->toString() &&
            $this->content()->toString() === $message->content()->toString();
    }
}
