<?php

declare(strict_types=1);

namespace Entity;

use Ramsey\Uuid\Uuid;
use ValueObject\Event;
use ValueObject\Rules;
use ValueObject\Stock;

class Match
{
    public const STATUS_NEW = "new";

    /** @var string */
    public $id;
    /** @var string */
    public $lastUpdatedHash;
    /** @var \DateTime */
    public $createdAt;
    /** @var string */
    public $rules;
    /** @var string */
    public $status;
    /** @var Player[] */
    public $players;
    /** @var Stock */
    public $stock;
    /** @var Event[] */
    public $events;

    public static function create(Rules $rules, Player $mainPlayer): Match
    {
        $match                  = new Match();
        $match->id              = Uuid::uuid4()->toString();
        $match->lastUpdatedHash = Uuid::uuid4()->toString();
        $match->createdAt       = new \DateTime();
        $match->rules           = $rules->name;
        $match->status          = self::STATUS_NEW;
        $match->players         = [$mainPlayer];
        $match->stock           = Stock::create($rules->getAllTiles());
        $match->events          = [];

        return $match;
    }
}
