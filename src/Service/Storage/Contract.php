<?php

declare(strict_types=1);

namespace Service\Storage;

use Entity\Match;

interface Contract
{
    public function getMatch(string $id): ?Match;

    public function setMatch(Match $match): void;
}
