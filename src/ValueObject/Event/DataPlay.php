<?php

declare(strict_types=1);

namespace ValueObject\Event;

use ValueObject\Tile;

class DataPlay
{
    /** @var Tile */
    public $tile;
    /** @var Tile */
    public $parent;
    /** @var string */
    public $position;
}
