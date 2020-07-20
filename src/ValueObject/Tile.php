<?php

declare(strict_types=1);

namespace ValueObject;

class Tile
{
    /** Normal orientation: left side is in the left and right in the right.  */
    public const ORIENTATION_NORMAL = "normal";
    /** Reverse orientation: left side is in the right and right in the left.  */
    public const ORIENTATION_REVERSE = "reverse";

    /** @var int */
    public $left;
    /** @var int */
    public $right;
    /**
     * Orientation is empty until used in event (played).
     *
     * @var string
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
        if ($this->orientation == self::ORIENTATION_REVERSE) {
            $left = $this->right;
        } else {
            $left = $this->left;
        }

        return $left;
    }

    public function getOrientedRight(): int
    {
        if ($this->orientation == self::ORIENTATION_REVERSE) {
            $right = $this->left;
        } else {
            $right = $this->right;
        }

        return $right;
    }

    public function setRandOrientation(): Tile
    {
        if (rand(0, 1) == 1) {
            $this->orientation = self::ORIENTATION_REVERSE;
        } else {
            $this->orientation = self::ORIENTATION_NORMAL;
        }

        return $this;
    }
}
