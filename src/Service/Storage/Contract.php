<?php

declare(strict_types=1);

namespace Service\Storage;

use Entity\Match\Match;

interface Contract
{
    public function getMatch(): ?Match;

    public function setMatch(Match $match): void;
}
