<?php

declare(strict_types=1);

namespace Controller\JsonRpc;

use Datto\JsonRpc\Evaluator;
use Datto\JsonRpc\Exceptions\MethodException;
use DI\Container;
use Service\Game;
use Transformer\Arrayable;
use Transformer\Resource\Match;
use Transformer\Resource\MatchNew;
use ValueObject\Result;
use ValueObject\Tile;

class Api implements Evaluator
{
    protected const METHOD_NEW_MATCH       = "new-match";
    protected const METHOD_GET_MATCH       = "get-match";
    protected const METHOD_REGISTER_PLAYER = "register-player";
    protected const METHOD_PLAY = "play";

    /** @var Container */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $method
     * @param array  $arguments
     *
     * @throws ArgumentException
     * @throws MethodException
     * @throws ServerErrorException
     * @throws \Datto\JsonRpc\Exceptions\Exception
     */
    public function evaluate($method, $arguments)
    {
        if (!is_array($arguments)) {
            throw new ArgumentException();
        }

        try {
            switch ($method) {
                case self::METHOD_NEW_MATCH:
                    $response = $this->startNewMatch($arguments);

                    break;

                case self::METHOD_GET_MATCH:
                    $response = $this->getMatch($arguments);

                    break;

                case self::METHOD_REGISTER_PLAYER:
                    $response = $this->registerPlayer($arguments);

                    break;

                case self::METHOD_PLAY:
                    $response = $this->play($arguments);

                    break;

                default:
                    throw new MethodException();

                    break;
            }
        } catch (\Datto\JsonRpc\Exceptions\Exception $e) {
            throw $e;
        } catch (\Exception $e) {
            //@todo Log.
            throw new ServerErrorException();
        } catch (\Error $e) {
            //@todo Log.
            throw new ServerErrorException();
        }

        try {
            return $response->toArray();
        } catch (\Exception $e) {
            //@todo Log Serializing.
            throw new ServerErrorException();
        }
    }

    /**
     * Returns created main Player.
     *
     * @param $arguments
     *
     * @throws ArgumentException
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws ServerErrorException
     */
    protected function startNewMatch(array $arguments): Arrayable
    {
        $this->checkRequiredParams(['name', 'players', 'rules'], $arguments);

        $factory = $this->container->get('GameFactory');
        /** @var Game $game */
        $game = $factory->createByRulesName($arguments['rules']);

        if ($game == null) {
            throw new ArgumentException(gettext('Rules are not valid'));
        }
        $result = $game->startNewMatch($arguments['name'], $arguments['players']);

        return MatchNew::create($this->checkResult($result)->getObject());
    }

    /**
     * @throws ArgumentException
     */
    public function getMatch(array $arguments): Arrayable
    {
        $game = $this->getGameByArguments($arguments);

        return Match::create($game->getMatch())->setPlayerId($arguments['playerId'] ?? '');
    }

    public function registerPlayer(array $arguments): Arrayable
    {
        $this->checkRequiredParams(['name'], $arguments);
        $game = $this->getGameByArguments($arguments);
        $this->checkResult($game->registerNewPlayer($arguments['name']));

        if ($game->getMatch()->status != \Entity\Match::STATUS_NEW) {
            // First move has done, we should do autoplay.
            $game->autoPlay();
        }

        return MatchNew::create($game->getMatch());
    }

    public function play(array $arguments): Arrayable
    {
        $this->checkRequiredParams(['tile', 'position', 'playerId'], $arguments);
        $game = $this->getGameByArguments($arguments);
        $this->checkResult($game->play(
            $this->hydrateTile($arguments),
            $arguments['position'],
            $arguments['playerId'])
        );

        // Play is successful, we can do autoplay.
        $game->autoPlay();
        return Match::create($game->getMatch())->setPlayerId($arguments['playerId'] ?? '');
    }

    protected function getGameByArguments(array $arguments): Game
    {
        $this->checkRequiredParams(['gameId'], $arguments);

        /** @var Result $result */
        $result = $this->container->get('GameFactory')->createByMatchId(
            $arguments['gameId'],
            $arguments['playerId'] ?? '',
            $arguments['playerSecret'] ?? ''
        );

        return $this->checkResult($result)->getObject();
    }

    /**
     * Check that arguments has required params and generates ArgumentException if hasn't.
     *
     * @throws ArgumentException
     */
    protected function checkRequiredParams(array $requires, array $arguments): void
    {
        foreach ($requires as $require) {
            if (!isset($arguments[$require]) || empty($arguments[$require])) {
                throw new ArgumentException(sprintf(gettext('Param "%s" is undefined'), $require));
            }
        }
    }

    /**
     * If Result has error (validation or system) - generates corresponding exception.
     *
     * @throws ArgumentException
     * @throws ServerErrorException
     */
    protected function checkResult(Result $result): Result
    {
        if ($result->hasError()) {
            if ($result->isSystemError()) {
                throw new ServerErrorException();
            }

            throw new ArgumentException($result->getError());
        }

        return $result;
    }

    /**
     * @param array $arguments
     * @return Tile
     * @throws ArgumentException
     */
    protected function hydrateTile(array $arguments): Tile
    {
        $str = $arguments['tile'] ?? '';
        if (!is_string($str) || strlen($str) != 3 || (int)$str[0] > 6 || (int)$str[2] > 6 || $str[1] != ':') {
            throw new ArgumentException(gettext('Tile is not valid, should be in format from "0:0" to "6:6"'));
        }
        return Tile::create((int)$str[0], (int)$str[2])->normalize();
    }
}
