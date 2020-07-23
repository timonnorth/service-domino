<?php

declare(strict_types=1);

namespace Service;

use Entity\Match;
use Infrastructure\Metrics\Metrics;
use Infrastructure\Metrics\MetricsNames;
use Infrastructure\Metrics\MetricsTrait;
use Infrastructure\Persistence\MatchRepositoryManager;
use Service\Repository\MatchRepositoryInterface;
use Symfony\Component\Lock\LockFactory;
use ValueObject\Result;

class GameFactory
{
    use MetricsTrait;

    /** @var RulesLoader */
    protected $rulesLoader;
    /** @var MatchRepositoryInterface */
    protected $matchRepository;
    /** @var LockFactory */
    protected $locker;
    /** @var Metrics */
    protected $metrics;

    /**
     * @throws Repository\Exception
     */
    public function __construct(
        RulesLoader $rulesLoader,
        MatchRepositoryManager $matchRepositoryManager,
        LockFactory $locker,
        Metrics $metrics
    ) {
        $this->rulesLoader     = $rulesLoader;
        $this->matchRepository = $matchRepositoryManager->getRepository();
        $this->locker          = $locker;
        $this->metrics         = $metrics;
    }

    /**
     * Game created without Match, plz start it before using Game.
     * Returns NULL if rulesName is not valid.
     *
     * @throws \Transformer\Encoder\Exception
     */
    public function createByRulesName(string $rulesName): ?Game
    {
        $rules = $this->rulesLoader->getRules($rulesName);

        if ($rules) {
            $game        = new Game($this->matchRepository, $this->locker, $this->metrics, null);
            $game->rules = $rules;
            $this->metrics->counter(MetricsNames::GAME_CREATED_OK);
        } else {
            $game = null;
            $this->metrics->counter(MetricsNames::GAME_CREATE_VALIDATION);
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
            $match = $this->matchRepository->load($matchId);

            if (
                $match === null || ($match->status != Match::STATUS_NEW && !$match->amIplayer($playerId, $playerSecret))
            ) {
                // Everybody has access to NEW Match, but only players to another statuses.
                if ($match) {
                    $message = gettext('No free slot to register new player');
                } else {
                    $message = gettext('Match not found');
                }
                $result = Result::create(null, $message);
            } else {
                $game        = new Game($this->matchRepository, $this->locker, $this->metrics, $match);
                $game->rules = $this->rulesLoader->getRules($match->rules);

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

        $this->metrics->counter($this->getMetricsNameByResult(
            $result,
            MetricsNames::GAME_GET_OK,
            MetricsNames::GAME_GET_PROBLEM,
            MetricsNames::GAME_GET_ERROR
        ));

        return $result;
    }
}
