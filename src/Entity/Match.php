<?php

declare(strict_types=1);

namespace Entity;

use Ramsey\Uuid\Uuid;
use ValueObject\Event;
use ValueObject\Rules;
use ValueObject\Stock;

class Match
{
    public const STATUS_NEW  = 'new';
    public const STATUS_PLAY = 'play';

    /** @var string */
    public $id;
    /** @var string */
    public $lastUpdatedHash;
    /** @var int */
    public $createdAt;
    /** @var string */
    public $rules;
    /** @var string */
    public $status;
    /** @var Player[] */
    public $players;
    /** @var Stock */
    public $stock;
    /** @var Event[] */
    public $events;

    public static function create(Rules $rules, Player $mainPlayer): Match
    {
        $match                  = new Match();
        $match->id              = Uuid::uuid4()->toString();
        $match->lastUpdatedHash = Uuid::uuid4()->toString();
        $match->createdAt       = time();
        $match->rules           = $rules->name;
        $match->status          = self::STATUS_NEW;
        $match->players         = [$mainPlayer];
        $match->stock           = Stock::create($rules->getAllTiles());
        $match->events          = [];

        return $match;
    }

    public function getPlayer(string $playerId): ?Player
    {
        $result = null;

        foreach ($this->players as $player) {
            if ($player->id == $playerId) {
                $result = $player;

                break;
            }
        }

        return $result;
    }

    /**
     * Register new player in non-active player-slot. Otherwise does not add.
     */
    public function registerPlayer(Player $player): void
    {
        foreach ($this->players as $key => $value) {
            if ($value->id === null) {
                // Can inject player in non-active slot.
                $this->players[$key] = $player;

                break;
            }
        }
    }

    public function amIplayer(string $playerId, string $playerSecret): bool
    {
        $result = false;
        $player = $this->getPlayer($playerId);

        if ($player && $player->secret == $playerSecret) {
            $result = true;
        }

        return $result;
    }

    public function getCountRegisteredPlayers(): int
    {
        $count = 0;

        foreach ($this->players as $player) {
            if ($player->id != '') {
                $count++;
            }
        }

        return $count;
    }

    public function addPlayEvent(Event\DataPlay $data, string $playerId)
    {
        $this->events[] = Event::create(Event::TYPE_PLAY, $data, $playerId);
    }

    /**
     * Moves marker to the next player.
     */
    public function moveMarker()
    {
        $ind                         = $this->getPlayerIndexMarker();
        $this->players[$ind]->marker = false;

        if (++$ind >= count($this->players)) {
            $ind = 0;
        }
        $this->players[$ind]->marker = true;
    }

    /**
     * Returns key in players array who has marker.
     */
    protected function getPlayerIndexMarker(): int
    {
        $ind = 0;

        foreach ($this->players as $key => $player) {
            if ($player->marker) {
                $ind = $key;

                break;
            }
        }

        return $ind;
    }
}
