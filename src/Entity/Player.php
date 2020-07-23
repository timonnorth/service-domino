<?php

declare(strict_types=1);

namespace Entity;

use Ramsey\Uuid\Uuid;
use Transformer\Resource\Event\Play;
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
    /**
     * System flag, sets true when player can't play by his/her Tiles. The idea is when all players are not playable,
     * we will detect it and finish the match.
     *
     * @var bool
     */
    protected $deadlock;

    public static function create(string $name): Player
    {
        $player         = new Player();
        $player->id     = Uuid::uuid4()->toString();
        $player->secret = Uuid::uuid4()->toString();
        $player->name   = trim($name);
        $player->marker = false;
        $player->tiles  = new Tiles();

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

    /**
     * Set marker to given player. Check and remove from another players.
     */
    public function setMarker(array $existPlayers = []): Player
    {
        foreach ($existPlayers as $existPlayer) {
            $existPlayer->marker = false;
        }
        $this->marker = true;

        return $this;
    }

    public function setDeadlock(): Player
    {
        $this->deadlock = true;

        return $this;
    }

    public function isDeadlock(): bool
    {
        return (bool)$this->deadlock;
    }
}
