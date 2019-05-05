<?php
declare(strict_types=1);

namespace YapepBase\Event;

interface IEventHandler
{
    public function handleEvent(Event $event): void;
}
