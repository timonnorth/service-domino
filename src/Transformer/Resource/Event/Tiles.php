<?php

declare(strict_types=1);

namespace Transformer\Resource\Event;

use Transformer\Resource\ResourceAbstract;
use Transformer\Resource\TileTrait;
use ValueObject\Event\DataTiles;

/**
 * Class Tiles
 *
 * @property DataTiles $object
 */
class Tiles extends ResourceAbstract
{
    use TileTrait;

    public static function create(DataTiles $dataTiles): Tiles
    {
        return new Tiles($dataTiles);
    }

    public function toArray(): array
    {
        $list = [];
        foreach ($this->object->tiles as $tile) {
            $list[] = $this->serializeTile($tile);
        }
        return $list;
    }
}
