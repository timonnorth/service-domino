<?php

declare(strict_types=1);

namespace ValueObject;

class Event
{
    public const TYPE_PLAY = 'play';
    public const TYPE_DRAW = 'draw';
    public const TYPE_SKIP = 'skip';
    public const TYPE_WIN  = 'win';

    /** @var string */
    public $type;
    /** @var int */
    public $createdAt;
    /** @var mixed */
    public $data;
    /** @var string */
    public $playerId;

    public static function create(string $type, $data, string $playerId): Event
    {
        $event            = new Event();
        $event->createdAt = time();
        $event->type      = $type;
        $event->data      = $data;
        $event->playerId  = $playerId;

        return $event;
    }
}
