<?php

declare(strict_types=1);

namespace Service\Family;

use Entity\Match;
use ValueObject\Event\DataScore;
use ValueObject\Rules;
use ValueObject\Tile;

/**
 * Family from task:
 *
 * The 28 tiles are shuffled face down and form the stock. Each player draws seven tiles.
 * Pick a random tile to start the line of play.
 * The players alternately extend the line of play with one tile at one of its two ends;
 * A tile may only be placed next to another tile, if their respective values on the connecting ends are identical.
 * If a player is unable to place a valid tile, they must keep on pulling tiles from the stock until they can.
 * The game ends when one player wins by playing their last tile.
 */
class FamilyBasic implements FamilyInterface
{
    use FamilyTrait;

    /**
     * First Tile is always from Stock.
     * Marker to first Player or random.
     *
     * @param Match &$match
     */
    public function firstStep(Rules $rules, Match &$match): Tile
    {
        $tile           = $match->stock->tiles->pop()[0];
        $player         = $rules->isFirstMoveRandom ? $this->getRandomPlayer($match->players) : $match->players[0];
        $player->marker = true;
        $this->addFirstPlayEvent($match, $tile, $player->id);

        return $tile;
    }

    public function isDrawingPublic(): bool
    {
        return true;
    }

    public function calculateScore(string $winnerPlayerId, Match $match): ?DataScore
    {
        return null;
    }
}
