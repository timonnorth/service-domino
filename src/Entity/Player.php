<?php

declare(strict_types=1);

namespace Entity;

use Ramsey\Uuid\Uuid;
use ValueObject\Tiles;

class Player
{
    /** @var string */
    public $id;
    /** @var string */
    public $secret;
    /** @var string */
    public $name;
    /** @var bool */
    public $marker;
    /** @var Tiles */
    public $tiles;

    public static function create(string $name): Player
    {
        $player         = new Player();
        $player->id     = Uuid::uuid4()->toString();
        $player->secret = Uuid::uuid4()->toString();
        $player->name   = trim($name);
        $player->marker = false;
        $player->tiles  = [];

        return $player;
    }

    public function selfValidate(): ?string
    {
        $result = null;
        $length = strlen($this->name);

        if ($length < 2) {
            $result = gettext("Name should be minimum 2 character");
        } elseif ($length > 80) {
            $result = gettext("Name should be maximum 80 character");
        }

        return $result;
    }
}
