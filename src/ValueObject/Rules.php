<?php

declare(strict_types=1);

namespace ValueObject;

class Rules
{
    protected const DEFAULT_NAME                   = "default";
    protected const DEFAULT_FAMILY                 = "traditional";
    protected const DEFAULT_COUNT_MAX_PLAYERS      = 2;
    protected const DEFAULT_COUNT_TILES_WHEN_START = 7;

    /** @var string */
    public $name;
    /** @var string */
    public $family;
    /** @var int */
    public $countMaxPlayers;
    /** @var int */
    public $countTilesWhenStartDefault;
    /**
     * First value is count of tiles for 2 players, than for 3 etc, up to $countMaxPlayers.
     *
     * @var array
     */
    public $countTilesWhenByPlayers;
    /** @var bool */
    public $isFirstMoveRandom;
    /** @var Tiles */
    protected $tilesAll;

    public static function createByParameters(array $parameters): Rules
    {
        $rules = new Rules();

        if (isset($parameters['name']) && $parameters['name'] != '') {
            $rules->name = (string)$parameters['name'];
        } else {
            $rules->name = self::DEFAULT_NAME;
        }

        if (isset($parameters['family']) && $parameters['family'] != '') {
            $rules->family = (string)$parameters['family'];
        } else {
            $rules->family = self::DEFAULT_FAMILY;
        }

        if (isset($parameters['count_max_players']) && $parameters['count_max_players'] > 0) {
            $rules->countMaxPlayers = (int)$parameters['count_max_players'];
        } else {
            $rules->countMaxPlayers = self::DEFAULT_COUNT_MAX_PLAYERS;
        }

        if (isset($parameters['count_tiles_when_start']) && $parameters['count_tiles_when_start'] > 0) {
            $rules->countTilesWhenStartDefault = (int)$parameters['count_tiles_when_start'];
        } else {
            $rules->countTilesWhenStartDefault = self::DEFAULT_COUNT_TILES_WHEN_START;
        }

        if (isset($parameters['count_tiles_by_players']) && is_array($parameters['count_tiles_by_players'])) {
            $rules->countTilesWhenByPlayers = $parameters['count_tiles_by_players'];
        }

        if (isset($parameters['is_first_move_random'])) {
            $rules->isFirstMoveRandom = (bool)$parameters['is_first_move_random'];
        } else {
            $rules->isFirstMoveRandom = false;
        }
        // We do not allow to customize tiles in this version.
        return $rules;
    }

    /**
     * According to rules count of "starting" tiles can be different.
     *
     * @return int
     */
    public function getCountTilesWhenStart(int $countPlayers): int
    {
        if (isset($this->countTilesWhenByPlayers[$countPlayers - 2])) {
            $count = $this->countTilesWhenByPlayers[$countPlayers - 2];
        } else {
            $count = $this->countTilesWhenStartDefault;
        }
        return $count;
    }

    public function getAllTiles(): Tiles
    {
        if ($this->tilesAll === null) {
            $this->generateDefaultTiles();
        }

        return $this->tilesAll;
    }

    protected function generateDefaultTiles()
    {
        $this->tilesAll = new Tiles();
        // More faster than for -> for.
        $this->tilesAll->list = [
            Tile::create(0, 0),
            Tile::create(0, 1),
            Tile::create(0, 2),
            Tile::create(0, 3),
            Tile::create(0, 4),
            Tile::create(0, 5),
            Tile::create(0, 6),
            Tile::create(1, 1),
            Tile::create(1, 2),
            Tile::create(1, 3),
            Tile::create(1, 4),
            Tile::create(1, 5),
            Tile::create(1, 6),
            Tile::create(2, 2),
            Tile::create(2, 3),
            Tile::create(2, 4),
            Tile::create(2, 5),
            Tile::create(2, 6),
            Tile::create(3, 3),
            Tile::create(3, 4),
            Tile::create(3, 5),
            Tile::create(3, 6),
            Tile::create(4, 4),
            Tile::create(4, 5),
            Tile::create(4, 6),
            Tile::create(5, 5),
            Tile::create(5, 6),
            Tile::create(6, 6),
        ];
    }
}
