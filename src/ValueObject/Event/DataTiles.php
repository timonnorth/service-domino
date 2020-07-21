<?php

declare(strict_types=1);

namespace ValueObject\Event;

use ValueObject\Tile;

class DataTiles
{
    /** @var Tile[] */
    public $tiles;

    public static function create(array $tiles): DataTiles
    {
        $data        = new DataTiles();
        $data->tiles = $tiles;

        return $data;
    }
}
