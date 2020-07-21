<?php

declare(strict_types=1);

namespace Transformer\Resource;

use ValueObject\Tile;

trait TileTrait
{
    /**
     * Represents Tile as string (for json etc).
     *
     * @return string
     */
    public function serializeTile(?Tile $tile): ?string
    {
        return $tile === null ? null : sprintf('%d:%d', $tile->getOrientedLeft(), $tile->getOrientedRight());
    }
}
