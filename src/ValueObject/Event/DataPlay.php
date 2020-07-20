<?php

declare(strict_types=1);

namespace ValueObject\Event;

use ValueObject\Tile;

class DataPlay
{
    public const POSITION_ROOT  = 'root';
    public const POSITION_LEFT  = 'left';
    public const POSITION_RIGHT = 'right';

    /** @var Tile */
    public $tile;
    /** @var ?Tile */
    public $parent;
    /**
     * In some Domino family it can be more than 2 positions.
     *
     * @var string
     */
    public $position;

    public static function create(Tile $tile, ?Tile $parent, string $position): DataPlay
    {
        $data           = new DataPlay();
        $data->tile     = $tile;
        $data->parent   = $parent;
        $data->position = $position;

        return $data;
    }
}
