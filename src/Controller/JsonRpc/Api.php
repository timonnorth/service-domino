<?php

declare(strict_types=1);

namespace Controller\JsonRpc;

use Datto\JsonRpc\Evaluator;
use Datto\JsonRpc\Exceptions\MethodException;
use DI\Container;
use Entity\Match;
use Service\Game;

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
     */
    public function evaluate($method, $arguments)
    {
        if (!is_array($arguments)) {
            throw new ArgumentException();
        }

        try {
            switch ($method) {
                case self::METHOD_NEW_MATCH:
                    $response = $this->newMatch($arguments);
                    break;
            }
        } catch (\Exception $e) {
            //@todo Log.
            throw new ServerErrorException();
        }

        if (!isset($response)) {
            throw new MethodException();
        }

        return $response;
    }

    /**
     * @param $arguments
     * @return Match
     * @throws ArgumentException
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    protected function newMatch($arguments): Match
    {
        if (!isset($arguments['name'])) {
            throw new ArgumentException(gettext('Name param undefined'));
        }
        if (!isset($arguments['players'])) {
            throw new ArgumentException(gettext('Players param undefined'));
        }
        if (!isset($arguments['rules'])) {
            throw new ArgumentException(gettext('Rules param undefined'));
        }

        $factory = $this->container->get('GameFactory');
        /** @var Game $game */
        $game = $factory->createByRulesName($arguments['rules']);
        if (null == $game) {
            throw new ArgumentException(gettext('Rules are not valid'));
        }
        $result = $game->startNewMatch($arguments['name'], $arguments['players']);
        if ($result->hasError()) {
            throw new ArgumentException($result->getError());
        } else {
            $res = $result->getObject();
        }

        //$res = new \stdClass();
        //$res->asd = "opana";
        return $res;
    }
}
