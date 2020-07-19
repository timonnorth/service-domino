<?php

declare(strict_types=1);

namespace ValueObject;

class Stock implements \Countable
{
    /** @var Tiles */
    public $tiles;

    public function count(): int
    {
        return $this->tiles->count();
    }
}
