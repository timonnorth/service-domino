<?php

declare(strict_types=1);

namespace ValueObject;

class Edge
{
    /** @var int */
    public $left;
    /** @var int */
    public $right;

    /**
     * Returns true if you can "play" with given Tile.
     * Means Tile has values what are equal to left or right Edge.
     */
    public function canPlayByTile(Tile $tile): bool
    {
        return $tile->left  == $this->left
            || $tile->right == $this->right
            || $tile->right == $this->left
            || $tile->right == $this->right;
    }
}
