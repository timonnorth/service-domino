<?php

declare(strict_types=1);

namespace Transformer\Resource\Event;

use Transformer\Resource\ResourceAbstract;
use Transformer\Resource\TileTrait;
use ValueObject\Event\DataPlay;

/**
 * Class Play
 *
 * @property DataPlay $object
 */
class Play extends ResourceAbstract
{
    use TileTrait;

    public static function create(DataPlay $dataPlay): Play
    {
        return new Play($dataPlay);
    }

    public function toArray(): array
    {
        return [
            'tile' => $this->serializeTile($this->object->tile),
            'position' => $this->object->position,
            'parent' => $this->serializeTile($this->object->parent)
        ];
    }
}
