<?php

declare(strict_types=1);

namespace Transformer\Entity;

use Transformer\Arrayable;

class Player implements Arrayable
{
    /** @var \Entity\Player */
    protected $player;

    public function __construct(\Entity\Player $player)
    {
        $this->player = $player;
    }

    public static function create(\Entity\Player $player): Player
    {
        return new Player($player);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->player->id,
            'name' => $this->player->name,
            'marker' => $this->player->marker
        ];
    }
}
