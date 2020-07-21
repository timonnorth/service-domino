<?php

declare(strict_types=1);

namespace Controller\JsonRpc;

use Datto\JsonRpc\Evaluator;
use Datto\JsonRpc\Exceptions\MethodException;
use DI\Container;
use Entity\Match;
use Service\Game;
use Transformer\Arrayable;
use Transformer\Entity\Player;
use Transformer\Entity\PlayerMain;

class Api implements Evaluator
{
    protected const METHOD_NEW_MATCH = "new-match";

    /** @var Container */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
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
            }
        } catch (\Datto\JsonRpc\Exceptions\Exception $e) {
            throw $e;
        } catch (\Exception $e) {
            //@todo Log.
            throw new ServerErrorException();
        }

        if (!isset($response)) {
            throw new MethodException();
        }

        return $response->toArray();
    }

    /**
     * Returns created main Player.
     *
     * @param $arguments
     * @return Arrayable
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
        if (null == $game) {
            throw new ArgumentException(gettext('Rules are not valid'));
        }
        $result = $game->startNewMatch($arguments['name'], $arguments['players']);
        if ($result->hasError()) {
            if ($result->isSystemError()) {
                throw new ServerErrorException();
            }
            throw new ArgumentException($result->getError());
        }

        return PlayerMain::create($result->getObject()->players[0]);
    }
    

    /**
     * @param array $requires
     * @param array $arguments
     * @throws ArgumentException
     */
    protected function checkRequiredParams(array $requires, array $arguments): void
    {
        foreach ($requires as $require) {
            if (!isset($arguments[$require])) {
                throw new ArgumentException(sprintf(gettext('Param "%s" is undefined'), $require));
            }
        }
    }
}
