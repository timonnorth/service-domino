<?php

declare(strict_types=1);

namespace Service;

use Entity\Match;
use Infrastructure\Metrics\Metrics;
use Infrastructure\Metrics\MetricsNames;
use Infrastructure\Metrics\MetricsTrait;
use Service\Game\Exception;
use Service\Game\GameTrait;
use Service\Storage\StorageInterface;
use Symfony\Component\Lock\LockFactory;
use ValueObject\Event\DataPlay;
use ValueObject\Result;
use ValueObject\Rules;
use ValueObject\Tile;

class Game
{
    use GameTrait, MetricsTrait;

    protected const LOCK_MATCH_TTL = 10;

    /**
     * Game constructor.
     * If Match present it will be locked.
     */
    public function __construct(StorageInterface $storage, LockFactory $locker, Metrics $metrics, ?Match $match)
    {
        $this->storage = $storage;
        $this->locker  = $locker;
        $this->metrics = $metrics;
        $this->match   = $match;

        if ($this->match !== null) {
            $this->matchLock = $this->locker->createLock($this->match->id, (float)static::LOCK_MATCH_TTL);
        }
    }

    public function __destruct()
    {
        $this->unlockMatch();
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
                $this->metrics->gauge(MetricsNames::GAME_PLAYERS_IN_MATCH, $countPlayers);

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
                        $this->tilesDrawFirstStep();
                    }
                    $this->storage->setMatch($this->match);
                }
            }
        } catch (\Exception $e) {
            //@todo Log exception.
            $result = Result::create(null, gettext($e->getMessage()), true);
        }

        $this->metrics->counter($this->getMetricsNameByResult(
            $result,
            MetricsNames::GAME_REGISTER_PLAYER_OK,
            MetricsNames::GAME_REGISTER_PLAYER_PROBLEM,
            MetricsNames::GAME_REGISTER_PLAYER_ERROR)
        );
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

            if (!$tile->hasEdge($position == DataPlay::POSITION_RIGHT ? $edge->right : $edge->left)) {
                $result = Result::create(null, gettext("You can not play by this Tile in this position"));
            } elseif (!$result->getObject()->tiles->remove($tile)) {
                // This must not happen, because we checked Tile in validation. So, system exception.
                throw new Exception('Trying to play with not valid Tile');
            } else {
                // Create event, move marker and save.
                $this->match->addPlayEvent(
                    DataPlay::create(
                        $this->match->getEdge()->normalize($tile, $position),
                        $position == DataPlay::POSITION_RIGHT ? $edge->tileRight : $edge->tileLeft,
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

        $this->metrics->counter($this->getMetricsNameByResult(
            $result,
            MetricsNames::GAME_PLAY_OK,
            MetricsNames::GAME_PLAY_PROBLEM,
            MetricsNames::GAME_PLAY_ERROR)
        );
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
                    $this->finishMatch($player->id);
                    $this->metrics->counter(MetricsNames::GAME_FINISHED_MATCH_FISH);

                    break;
                }
                $drawedTiles = [];

                while (!$this->canPlay($player)) {
                    if ($this->match->stock->count() <= 0) {
                        // Stock is empty and Player still can't play, mark him/her and jump to next.
                        $player->setDeadlock();
                        $this->match->addDrawEvent($drawedTiles, $player->id, true);
                        $this->match->moveMarker();
                        $changed = true;

                        continue 2;
                    }
                    // I do not have Tile to play, let's go to draw from Stock.
                    $tiles = $this->match->stock->tiles->pop();

                    if (!$this->rules->getFamily()->isDrawingPublic()) {
                        // We anonymize drawing Tile.
                        $drawedTiles[] = (clone $tiles[0])->anonymize();
                    } else {
                        $drawedTiles = array_merge($drawedTiles, $tiles);
                    }
                    $player->tiles->push($tiles);
                    $changed = true;
                }
                $this->match->addDrawEvent($drawedTiles, $player->id);

                break;
            }

            if (isset($changed) && $changed) {
                $this->storage->setMatch($this->match);
            }
        }
    }
}
