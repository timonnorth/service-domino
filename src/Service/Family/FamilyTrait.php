<?php

declare(strict_types=1);

namespace Service\Family;

use Entity\Match;
use Entity\Player;
use ValueObject\Event\DataPlay;
use ValueObject\Tile;

trait FamilyTrait
{
    public function getRandomPlayer(array $players): Player
    {
        return $players[rand(0, count($players) - 1)];
    }

    public function addFirstPlayEvent(Match &$match, Tile $tile, string $playerId)
    {
        $match->addPlayEvent(
            DataPlay::create($tile->setRandOrientation(), null, DataPlay::POSITION_ROOT),
            $playerId
        );

    }
}
