<?php

declare(strict_types=1);

namespace Transformer\Resource;

/**
 * Class Match
 *
 * @property \Entity\Match $object
 */
class Match extends ResourceAbstract
{
    use AuthTrait;

    public static function create(\Entity\Match $match): Match
    {
        return new Match($match);
    }

    public function toArray(): array
    {
        return [
            'id'              => $this->object->id,
            'lastUpdatedHash' => $this->object->lastUpdatedHash,
            'createdAt'       => $this->object->createdAt,
            'rules'           => $this->object->rules,
            'status'          => $this->object->status,
            'players'         => Collection::create(
                $this->object->players,
                (new Player(null))->setPlayerId($this->getPlayerId())
            )->toArray(),
            'stock'  => Stock::create($this->object->stock)->toArray(),
            'events' => Collection::create($this->object->events, Event::class)->toArray(),
        ];
    }
}
