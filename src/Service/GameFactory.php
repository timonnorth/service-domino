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
        $this->locker  = $locker;
    }

    /**
     * Game created without Match, plz start it before using Game.
     * Returns NULL if rulesName is not valid.
     *
     * @throws \Transformer\Encoder\Exception
     */
    public function createByRulesName(string $rulesName): ?Game
    {
        $rules = $this->loadRules($rulesName);
        if ($rules) {
            $game              = new Game($this->storage, $this->locker, null);
            $game->rules       = $rules;
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
                $game = new Game($this->storage, $this->locker, $match);
                $game->rules = $this->loadRules($match->rules);
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

    /**
     * Load rules from resources by its name.
     *
     * @param string $rulesName
     * @return Rules|null
     * @throws \Transformer\Encoder\Exception
     */
    protected function loadRules(string $rulesName): ?Rules
    {
        $filename = sprintf('%s/resources/rules/%s.json', __APPDIR__, $rulesName);

        if (is_file($filename)) {
            $rules       = Rules::createByParameters($this->encoder->decode(file_get_contents($filename)));
            $rules->name = $rulesName;
        } else {
            $rules = null;
        }

        return $rules;
    }
}
