<?php

declare(strict_types=1);

namespace Service\Repository;

use Entity\Match;

interface MatchRepositoryInterface
{
    public function load(string $id): ?Match;

    public function save(Match $match): void;
}
