<?php

declare(strict_types=1);

namespace Service;

use Entity\Match;
use Entity\Player;
use Service\Game\Exception;
use Service\Storage\StorageInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use ValueObject\Event;
use ValueObject\Result;
use ValueObject\Rules;
use ValueObject\Tile;

class Game
{
    protected const LOCK_MATCH_TTL = 10;

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

    /**
     * Game constructor.
     * If Match present it will be locked.
     */
    public function __construct(StorageInterface $storage, LockFactory $locker, ?Match $match)
    {
        $this->storage = $storage;
        $this->locker  = $locker;
        $this->match   = $match;

        if ($this->match !== null) {
            $this->matchLock = $this->locker->createLock($this->match->id, (float)static::LOCK_MATCH_TTL);
        }
    }

    public function __destruct()
    {
        $this->unlockMatch();
    }

    public function getMatch(): Match
    {
        return $this->match;
    }

    public function unlockMatch()
    {
        if ($this->matchLock !== null) {
            $this->matchLock->release();
            unset($this->matchLock);
        }
    }

    /**
     * Creating and starting new Match with one main player.
     * Success Match is locked.
     *
     * @return Result Match
     */
    public function startNewMatch(string $playerName, int $countPlayers): Result
    {
        try {
            if (!($this->rules instanceof Rules)) {
                throw new Exception('Rules undefined to start new game');
            }
            $playerResult = $this->createPlayer($playerName);

            if ($playerResult->hasError()) {
                $result = $playerResult;
            } else {
                $this->match = Match::create($this->rules, $playerResult->getObject());
                $result      = $this->setCountPlayers($countPlayers);

                if (!$result->hasError()) {
                    $this->matchLock = $this->locker->createLock($this->match->id, (float)static::LOCK_MATCH_TTL);
                    $this->storage->setMatch($this->match);
                    $result = Result::create($this->match);
                }
            }
        } catch (\Exception $e) {
            //@todo Log exception.
            $result = Result::create(null, gettext($e->getMessage()), true);
        }

        return $result;
    }

    /**
     * Registers new player to existing "new" match or returns error.
     *
     * @return Result Player
     */
    public function registerNewPlayer(string $playerName): Result
    {
        try {
            if ($this->match == null) {
                throw new Exception('Match undefined to register new player');
            }

            if (
                $this->match->status != Match::STATUS_NEW
                || $this->match->getCountRegisteredPlayers() >= count($this->match->players)
            ) {
                $result = Result::create(null, gettext("No free slot to register new player"));
            } else {
                $result = $this->createPlayer($playerName, $this->match->players);

                if (!$result->hasError()) {
                    // Add player to match and save it.
                    $this->match->registerPlayer($result->getObject());

                    if ($this->match->getCountRegisteredPlayers() >= count($this->match->players)) {
                        // All player slots are completed, let mortal kombat begin.
                        $this->tilesDraw();
                    }
                    $this->storage->setMatch($this->match);
                }
            }
        } catch (\Exception $e) {
            //@todo Log exception.
            $result = Result::create(null, gettext($e->getMessage()), true);
        }

        return $result;
    }

    /**
     * Play (validate) with given Tile.
     * Do not forget to call autoPlay() when success result.
     *
     * @throws Exception
     *
     * @return Result Match
     */
    public function play(Tile $tile, string $position, string $playerId): Result
    {
        $result = $this->validatePlayRequest($tile, $position, $playerId);

        if (!$result->hasError()) {
            // Your Tile looks ok, let's try to play with it!
            $edge = $this->match->getEdge();

            if (!$tile->hasEdge($position == Event\DataPlay::POSITION_RIGHT ? $edge->right : $edge->left)) {
                $result = Result::create(null, gettext("You can not play by this Tile in this position"));
            } elseif (!$result->getObject()->tiles->remove($tile)) {
                // This must not happen, because we checked Tile in validation. So, system exception.
                throw new Exception('Trying to play with not valid Tile');
            } else {
                // Create event, move marker and save.
                $this->match->addPlayEvent(
                    Event\DataPlay::create(
                        $this->match->getEdge()->normalize($tile, $position),
                        $position == Event\DataPlay::POSITION_RIGHT ? $edge->tileRight : $edge->tileLeft,
                        $position
                    ),
                    $playerId
                );

                if ($result->getObject()->tiles->count() <= 0) {
                    // We have a winner!
                    $this->finishMatch($result->getObject()->id);
                } else {
                    $this->match->moveMarker();
                    $this->storage->setMatch($this->match);
                }
            }
        }

        return $result;
    }

    /**
     * Player can not keep Tile from Stock by UI, so system "plays" for him/her.
     * This method MUST be called after every "play" event.
     * It does not call automatically for better understanding how mechanism works (and for more comfortable testing).
     */
    public function autoPlay(): void
    {
        // Allow only for Play status, otherwise can autoplay after win.
        if ($this->match->status == Match::STATUS_PLAY) {
            while (true) {
                $player = $this->match->getMarkedPlayer();

                if ($player->isDeadlock()) {
                    // We have locked all players, game over.
                    $this->finishMatch();

                    break;
                }
                $drawedTiles = [];

                while (!$this->canIPlay($player)) {
                    if ($this->match->stock->count() <= 0) {
                        // Stock is empty and Player still can't play, mark him/her and jump to next.
                        $player->setDeadlock();
                        $this->match->addDrawEvent($drawedTiles, $player->id);
                        $this->match->moveMarker();

                        continue 2;
                    }
                    // I do not have Tile to play, let's go to draw from Stock.
                    $tiles       = $this->match->stock->tiles->pop();
                    $drawedTiles = array_merge($drawedTiles, $tiles);
                    $player->tiles->push($tiles);
                    $changed = true;
                }
                //@todo Fix in Event when tiles are open.
                $this->match->addDrawEvent($drawedTiles, $player->id);

                break;
            }

            if (isset($changed) && $changed) {
                $this->storage->setMatch($this->match);
            }
        }
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
    protected function calculateScore(string $playerId): Event\DataScore
    {
        $data = Event\DataScore::create(0, 0);

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
            } elseif ($position != Event\DataPlay::POSITION_LEFT && $position != Event\DataPlay::POSITION_RIGHT) {
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
            Event\DataPlay::create($tiles[0]->setRandOrientation(), null, Event\DataPlay::POSITION_ROOT),
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
