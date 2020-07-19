<?php

declare(strict_types=1);

namespace ValueObject;

class Stock implements \Countable
{
    /** @var Tiles */
    public $tiles;

    public static function create(Tiles $allTiles): Stock
    {
        $stock = new Stock();
        $stock->tiles = $allTiles;
        return $stock;
    }

    public function count(): int
    {
        return $this->tiles->count();
    }
}
