<?php

declare(strict_types=1);

namespace Service\Family;

use Entity\Match;
use Entity\Player;
use ValueObject\Event\DataScore;
use ValueObject\Rules;
use ValueObject\Tile;

/**
 * Traditional Domino rules.
 *
 * @see https://en.wikipedia.org/wiki/Dominoes#Rules
 */
class FamilyTraditional implements FamilyInterface
{
    use FamilyTrait;

    /**
     * First prio <1:1>, than upper to <6:6>. Tile <0:0> ("naked Vasili") does not play.
     * If nobody has double, smallest Tile plays.
     */
    public function firstStep(Rules $rules, Match $match): Tile
    {
        if ($rules->isFirstMoveRandom) {
            $player = $this->getRandomPlayer($match->players);
            $tile   = $player->tiles->pop()[0];
        } else {
            $tile = null;
            // Search first small double.
            for ($i = 1; $i <= 6; $i++) {
                $double = Tile::create($i, $i);

                foreach ($match->players as $player) {
                    if ($player->tiles->has($double)) {
                        $tile = $double;

                        break 2;
                    }
                }
            }

            if (!$tile) {
                // No doubles. Should calculate minimums.
                $min = 13;

                foreach ($match->players as $onePlayer) {
                    foreach ($onePlayer->tiles->list as $oneTile) {
                        $score = $oneTile->getScore();
                        // Ignore score for <0:0>
                        if ($score > 0 && $score < $min) {
                            $min    = $score;
                            $player = $onePlayer;
                            $tile   = $oneTile;
                        }
                    }
                }
            }
        }
        $player->tiles->remove($tile);
        $player->marker = true;
        $this->addFirstPlayEvent($match, $tile, $player->id);

        return $tile;
    }

    public function isDrawingPublic(): bool
    {
        return false;
    }

    /**
     * Plus: all loser players Tiles, if only <0:0> - 25 points.
     * Minus: winner Tiles (when "fish").
     */
    public function calculateScore(string $winnerPlayerId, Match $match): ?DataScore
    {
        $data = DataScore::create(0, 0);

        foreach ($match->players as $player) {
            if ($player->id != $winnerPlayerId) {
                $data->tilesLeft += $player->tiles->count();
                $data->score     += $this->getPlayerScore($player);
            } else {
                $data->score -= $this->getPlayerScore($player);
            }
        }

        return $data;
    }

    protected function getPlayerScore(Player $player): int
    {
        $score = 0;

        if ($player->tiles->count() == 1 && $player->tiles->list[0]->isEqual(Tile::create(0, 0))) {
            $score = 25;
        } else {
            foreach ($player->tiles->list as $tile) {
                $score += $tile->getScore();
            }
        }

        return $score;
    }
}
