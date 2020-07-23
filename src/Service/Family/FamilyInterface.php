<?php

declare(strict_types=1);

namespace Service\Family;

use Entity\Match;
use ValueObject\Event\DataScore;
use ValueObject\Rules;
use ValueObject\Tile;

/**
 * Interface FamilyInterface
 * Strategy for "family" of Domino's games. Every game's "family" has own changes how to do first step,
 * how to calculate winning score etc.
 *
 * @see https://worlddomino.com/domino-tennis/
 */
interface FamilyInterface
{
    /**
     * Returns Tail for first move. Tail should be selected according to Family's strategy.
     */
    public function firstStep(Rules $rules, Match &$match): Tile;

    /**
     * If TRUE - everybody can see what Tile(s) Player is drawing.
     */
    public function isDrawingPublic(): bool;

    /**
     * Family should calculate score (or return NULL for absent scoring).
     */
    public function calculateScore(string $winnerPlayerId, Match $match): ?DataScore;
}
