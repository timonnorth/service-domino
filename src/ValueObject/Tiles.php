<?php

declare(strict_types=1);

namespace ValueObject;

class Tiles implements \Countable
{
    /** @var Tile[] */
    public $list = [];

    public function count(): int
    {
        return count($this->list);
    }

    /**
     * Shuffle all tiles, must be done when game starts.
     */
    public function shuffle(): void
    {
        //array_rand()
    }
}
