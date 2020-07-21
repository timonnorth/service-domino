<?php

declare(strict_types=1);

namespace ValueObject;

use ValueObject\Event\DataPlay;

class Edge
{
    /** @var int */
    public $left;
    /** @var int */
    public $right;
    /** @var Tile */
    public $tileLeft;
    /** @var Tile */
    public $tileRight;

    /**
     * Returns true if you can "play" with given Tile.
     * Means Tile has values what are equal to left or right Edge.
     */
    public function canPlayByTile(Tile $tile): bool
    {
        return $tile->left == $this->left
            || $tile->right == $this->right
            || $tile->right == $this->left
            || $tile->right == $this->right;
    }

    /**
     * Set orientation of Tile according to position an edge.
     *
     * @param Tile $tile
     * @param string $position
     * @return Tile
     */
    public function normalize(Tile $tile, string $position): Tile
    {
        if ($tile->left != $tile->right
            && ($position == DataPlay::POSITION_LEFT && $tile->left == $this->left
                || $position == DataPlay::POSITION_RIGHT && $tile->right == $this->right)) {
            $tile->orientation = 1;
        } else {
            $tile->orientation = 0;
        }
        return $tile;
    }
}
