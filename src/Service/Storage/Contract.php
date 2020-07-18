<?php

declare(strict_types=1);

namespace Service\Storage;

use Entity\Match\Match;

interface Contract
{
    /**
     * @return Match|null
     */
    public function getMatch(): ?Match;

    /**
     * @param Match $match
     * @return void
     */
    public function setMatch(Match $match): void;
}
