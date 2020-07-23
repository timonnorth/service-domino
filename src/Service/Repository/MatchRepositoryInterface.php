<?php

declare(strict_types=1);

namespace Service\Repository;

use Entity\Match;

interface MatchRepositoryInterface
{
    public function getMatch(string $id): ?Match;

    public function setMatch(Match $match): void;
}
