<?php

declare(strict_types=1);

namespace Service;

use Entity\Match;
use Service\Storage\StorageInterface;
use Symfony\Component\Lock\LockFactory;
use ValueObject\Result;

class GameFactory
{
    /** @var RulesLoader */
    protected $rulesLoader;
    /** @var StorageInterface */
    protected $storage;
    /** @var LockFactory */
    protected $locker;

    public function __construct(RulesLoader $rulesLoader, StorageInterface $storage, LockFactory $locker)
    {
        $this->rulesLoader = $rulesLoader;
        $this->storage     = $storage;
        $this->locker      = $locker;
    }

    /**
     * Game created without Match, plz start it before using Game.
     * Returns NULL if rulesName is not valid.
     *
     * @throws \Transformer\Encoder\Exception
     */
    public function createByRulesName(string $rulesName): ?Game
    {
        $rules = $this->rulesLoader->loadRules($rulesName);

        if ($rules) {
            $game        = new Game($this->storage, $this->locker, null);
            $game->rules = $rules;
        } else {
            $game = null;
        }

        return $game;
    }

    /**
     * Match returns only when playerId and playerSecret are valid,
     *     otherwise 'Match not found' error returns.
     *
     * @return Result Game
     */
    public function createByMatchId(string $matchId, string $playerId = '', string $playerSecret = ''): Result
    {
        try {
            $match = $this->storage->getMatch($matchId);

            if (
                $match === null || $match->status != Match::STATUS_NEW && !$match->amIplayer($playerId, $playerSecret)
            ) {
                // Everybody has access to NEW Match, but only players to another statuses.
                if ($match) {
                    $message = gettext('No free slot to register new player');
                } else {
                    $message = gettext('Match not found');
                }
                $result = Result::create(null, $message);
            } else {
                $game        = new Game($this->storage, $this->locker, $match);
                $game->rules = $this->rulesLoader->loadRules($match->rules);

                if (!$game->rules) {
                    $result = Result::create(null, gettext('Rules not found'));
                } else {
                    $result = Result::create($game);
                }
            }
        } catch (\Exception $e) {
            //@todo Log exception.
            $result = Result::create(null, gettext($e->getMessage()), true);
        }

        return $result;
    }
}
