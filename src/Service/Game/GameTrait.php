<?php

declare(strict_types=1);

namespace Service\Game;

use Entity\Match;
use Entity\Player;
use Service\Storage\StorageInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use ValueObject\Event\DataPlay;
use ValueObject\Event\DataScore;
use ValueObject\Result;
use ValueObject\Rules;
use ValueObject\Tile;

trait GameTrait
{
    /** @var Rules */
    public $rules;
    /** @var StorageInterface */
    protected $storage;
    /** @var LockFactory */
    protected $locker;
    /** @var Match */
    protected $match;
    /** @var LockInterface */
    protected $matchLock;

    public function getMatch(): Match
    {
        return $this->match;
    }

    protected function finishMatch(string $playerId = ''): void
    {
        $this->match->status = Match::STATUS_FINISHED;
        $this->match->addWinEvent($this->calculateScore($playerId), $playerId);
        $this->storage->setMatch($this->match);
    }

    /**
     * @todo Must be done in Family.
     */
    protected function calculateScore(string $playerId): DataScore
    {
        $data = DataScore::create(0, 0);

        foreach ($this->match->players as $player) {
            if ($player->id != $playerId) {
                $data->tilesLeft += $player->tiles->count();

                foreach ($player->tiles->list as $tile) {
                    $data->score += $tile->left + $tile->right;
                }
            }
        }

        return $data;
    }

    /**
     * Checks can player play with some Tile on hands.
     * Returns false if player must to keep Tile from Stock (or game should be finished).
     */
    protected function canIPlay(Player $player): bool
    {
        $res = false;

        foreach ($player->tiles->list as $tile) {
            if ($this->match->getEdge()->canPlayByTile($tile)) {
                $res = true;

                break;
            }
        }

        return $res;
    }

    /**
     * Player result means there are no errors and Player can play.
     *
     * @return Result Player
     */
    protected function validatePlayRequest(Tile $tile, string $position, string $playerId): Result
    {
        if ($this->getMatch()->status != Match::STATUS_PLAY) {
            $result = Result::create(null, gettext('Match has finished or not started'));
        } else {
            $player = $this->getMatch()->getMarkedPlayer();

            if ($player->id != $playerId) {
                $result = Result::create(null, gettext('Waiting for another Player'));
            } elseif (!$player->tiles->has($tile)) {
                $result = Result::create(null, gettext('You do not have this Tile'));
            } elseif ($position != DataPlay::POSITION_LEFT && $position != DataPlay::POSITION_RIGHT) {
                $result = Result::create(null, gettext('Not valid position, should be "left" or "right"'));
            } else {
                $result = Result::create($player);
            }
        }

        return $result;
    }

    /**
     * Draw the tiles for players and activating the Match.
     * And call firstStep().
     */
    protected function tilesDraw(): void
    {
        $countTiles = $this->rules->getCountTilesWhenStart(count($this->match->players));

        foreach ($this->match->players as $key => $player) {
            $player->tiles->push($this->match->stock->tiles->pop($countTiles));
        }
        $this->firstStep();
    }

    /**
     * Detect player who does first step and do it. Move marker. Set status as "play".
     */
    protected function firstStep(): void
    {
        if (true || $this->rules->isFirstMoveRandom) {
            $ind   = rand(0, count($this->match->players) - 1);
            $tiles = $this->match->players[$ind]->tiles->pop();

            $this->match->players[$ind]->setMarker($this->match->players);
        }
        //@todo Family step

        $this->match->addPlayEvent(
            DataPlay::create($tiles[0]->setRandOrientation(), null, DataPlay::POSITION_ROOT),
            $this->match->players[$ind]->id
        );
        $this->match->status = Match::STATUS_PLAY;
        $this->match->moveMarker();
    }

    /**
     * Checks new player name and also that name is not busy by other players.
     * Set marker.
     *
     * @param Player[] $existPlayers
     *
     * @return Result Player
     */
    protected function createPlayer(string $playerName, array &$existPlayers = []): Result
    {
        $player     = Player::create($playerName);
        $validation = $player->selfValidate();

        if ($validation !== null) {
            $result = Result::create(null, $validation);
        } else {
            $result = Result::create($player->setMarker($existPlayers));

            foreach ($existPlayers as $existPlayer) {
                if ($player->name == $existPlayer->name) {
                    $result = Result::create(
                        null,
                        sprintf(gettext('Another player has already used name "%s"'), $player->name)
                    );

                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Set count of players for Match (can be done by main player).
     * Set 2 players if argument <= 0.
     * Validate with maxPlayers.
     */
    protected function setCountPlayers(int $countPlayers): Result
    {
        if ($countPlayers < 2) {
            $countPlayers = 2;
        }

        if ($countPlayers > $this->rules->countMaxPlayers) {
            $result = Result::create(
                null,
                sprintf(gettext('Count of players can not be more than %d'), $this->rules->countMaxPlayers)
            );
        } else {
            for ($i = 1; $i < $countPlayers; $i++) {
                $this->match->players[] = new Player();
            }
            $result = Result::create(null);
        }

        return $result;
    }
}
