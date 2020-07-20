<?php

declare(strict_types=1);

namespace Service;

use Entity\Match;
use Service\Storage\StorageInterface;
use Symfony\Component\Lock\LockFactory;
use Transformer\Encoder\EncoderInterface;
use ValueObject\Result;
use ValueObject\Rules;

class GameFactory
{
    /** @var EncoderInterface */
    protected $encoder;
    /** @var StorageInterface */
    protected $storage;
    /** @var LockFactory */
    protected $locker;

    public function __construct(EncoderInterface $jsonEncoder, StorageInterface $storage, LockFactory $locker)
    {
        $this->encoder = $jsonEncoder;
        $this->storage = $storage;
        $this->locker = $locker;
    }

    /**
     * Game created without Match, plz start it before using Game.
     *
     * @param string $rulesName
     * @return Game|null
     * @throws \Transformer\Encoder\Exception
     */
    public function createByRulesName(string $rulesName): ?Game
    {
        $filename = sprintf('%s/resources/rules/%s.json', __APPDIR__, $rulesName);

        if (is_file($filename)) {
            $game = new Game($this->storage, $this->locker, null);
            $game->rules = Rules::createByParameters($this->encoder->decode(file_get_contents($filename)));
            $game->rules->name = $rulesName;
        } else {
            $game = null;
        }

        return $game;
    }

    /**
     * Match returns only when playerId and playerSecret are valid,
     *     otherwise 'Match not found' error returns.
     *
     * @param string $matchId
     * @param string $playerId
     * @param string $playerSecret
     * @return Result Game
     */
    public function createByMatchId(string $matchId, string $playerId = '', string $playerSecret = ''): Result
    {
        try {
            $match = $this->storage->getMatch($matchId);
            if (
                null === $match ||
                Match::STATUS_NEW != $match->status && !$match->amIplayer($playerId, $playerSecret)
            ) {
                // Everybody has access to NEW Match, but only players to another statuses.
                $result = Result::create(null, gettext('Match not found'));
            } else {
                $result = Result::create(new Game($this->storage, $this->locker, $match));
            }
        } catch (\Exception $e) {
            //@todo Log exception.
            $result = Result::create(null, gettext($e->getMessage()), true);
        }

        return $result;
    }
}
