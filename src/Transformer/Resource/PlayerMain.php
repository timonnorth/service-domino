<?php

declare(strict_types=1);

namespace Transformer\Resource;

class PlayerMain extends Player
{
    public static function create(\Entity\Player $player): Player
    {
        return new PlayerMain($player);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'secret' => $this->object->secret,
        ]);
    }
}
