<?php

declare(strict_types=1);

namespace Transformer\Entity;

use Transformer\Arrayable;

class PlayerMain extends Player
{
    public static function create(\Entity\Player $player): Player
    {
        return new PlayerMain($player);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'secret' => $this->player->secret
        ]);
    }
}
