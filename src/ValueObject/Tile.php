<?php

declare(strict_types=1);

namespace ValueObject;

class Tile
{
    /** @var int */
    public $left;
    /** @var int */
    public $right;
    /**
     * Normal orientation  - 0: left side is in the left and right in the right.
     * Reverse orientation - 1: left side is in the right and right in the left.
     * Orientation is empty until used in event (played).
     *
     * @var int
     */
    public $orientation;

    public static function create(int $left, int $right): Tile
    {
        $tile        = new Tile();
        $tile->left  = $left;
        $tile->right = $right;

        return $tile;
    }

    public function getOrientedLeft(): int
    {
        return $this->orientation ? $this->right : $this->left;
    }

    public function getOrientedRight(): int
    {
        return $this->orientation ? $this->left : $this->right;
    }

    public function setRandOrientation(): Tile
    {
        $this->orientation = rand(0, 1);
        return $this;
    }

    public function isEqual(Tile $tile): bool
    {
        return $this->left === $tile->left && $this->right == $tile->right
            || $this->left === $tile->right && $this->right == $tile->left;
    }

    public function hasEdge(int $edge): bool
    {
        return $this->left == $edge || $this->right == $edge;
    }

    public function normalize(): Tile
    {
        if ($this->left > $this->right) {
            $tmp = $this->left;
            $this->left = $this->right;
            $this->right = $tmp;
        }
        return $this;
    }
}
