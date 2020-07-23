<?php

declare(strict_types=1);

namespace Service\Family;

use Entity\Match;
use ValueObject\Rules;
use ValueObject\Tile;

/**
 * Very popular russian variant.
 *
 * @see https://worlddomino.com/domino-tennis
 * @see https://ru.wikipedia.org/wiki/%D0%94%D0%BE%D0%BC%D0%B8%D0%BD%D0%BE#%D0%9A%D0%BE%D0%B7%D1%91%D0%BB
 */
class FamilyKozel extends FamilyTraditional
{
    /**
     * The difference from traditional is that one Tile removes from stock (if presents).
     * Usually 2-3 players play without one Tile and 4 players use all.
     *
     * @param Match &$match
     */
    public function firstStep(Rules $rules, Match &$match): Tile
    {
        $tile = parent::firstStep($rules, $match);

        if ($match->stock->count() > 0) {
            $match->stock->tiles->pop();
        }

        return $tile;
    }
}
