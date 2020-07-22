<?php

declare(strict_types=1);

namespace Service\Family;

use Entity\Match;
use ValueObject\Rules;
use ValueObject\Tile;

/**
 * Interface FamilyInterface
 * Strategy for "family" of Domino's games. Every game's "family" has own changes how to do first step,
 * how to calculate winning score etc.
 *
 * @see https://worlddomino.com/domino-tennis/
 * @package Service\Family
 */
interface FamilyInterface
{
    /**
     * Returns Tail for first move. Tail should be selected according to Family's strategy.
     *
     * @param Rules $rules
     * @param Match $match
     * @return Tile
     */
    public function firstStep(Rules $rules, Match &$match): Tile;
}
