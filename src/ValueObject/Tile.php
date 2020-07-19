<?php

declare(strict_types=1);

namespace ValueObject;

class Tile
{
    /** @var int */
    public $left;
    /** @var int */
    public $right;

    public static function create(int $left, int $right): Tile
    {
        $tile        = new Tile();
        $tile->left  = $left;
        $tile->right = $right;

        return $tile;
    }
}
