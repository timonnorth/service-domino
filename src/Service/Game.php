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
     * Player can not keep Tile from Stock by UI, so system "plays" for him/her.
     * This method MUST be called after every "play" event.
     * It does not call automatically for better understanding how mechanism works (and for more comfortable testing).
     */
    public function autoPlay(): void
    {
        $player = $this->match->getMarkedPlayer();
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
