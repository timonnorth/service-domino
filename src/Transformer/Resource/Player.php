<?php

declare(strict_types=1);

namespace Transformer\Resource;

/**
 * Class Player
 *
 * @property \Entity\Player $object
 */
class Player extends ResourceAbstract
{
    use AuthTrait;

    public static function create(\Entity\Player $player): Player
    {
        return new Player($player);
    }

    public function toArray(): array
    {
        $result = [
            'id'     => $this->object->id,
            'name'   => $this->object->name,
            'marker' => $this->object->marker,
            'tiles'  => null,
        ];
        // Sometimes tiles can be NULL.
        if ($this->object->tiles) {
            $result['tiles'] = $this->getPlayerId() == $this->object->id ?
                Tiles::create($this->object->tiles)->withList()->toArray() :
                Tiles::create($this->object->tiles)->toArray();
        }

        return $result;
    }
}
