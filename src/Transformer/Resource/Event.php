<?php

declare(strict_types=1);

namespace Transformer\Resource;

use Transformer\Resource\Event\DataFactory;
use Transformer\Resource\Event\Play;

/**
 * Class Event
 *
 * @property \ValueObject\Event $object
 */
class Event extends ResourceAbstract
{
    public static function create(\ValueObject\Event $event): Event
    {
        return new Event($event);
    }

    public function toArray(): array
    {
        return [
            'type' => $this->object->type,
            'createdAt' => $this->object->createdAt,
            'playerId' => $this->object->playerId,
            'data' => DataFactory::create($this->object->type, $this->object->data)->toArray()
        ];
    }
}
