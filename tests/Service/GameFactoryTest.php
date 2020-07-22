<?php

declare(strict_types=1);

namespace Tests\Service;

use Entity\Match;
use Service\Game;
use Service\GameFactory;
use Tests\TestCase;

class GameFactoryTest extends TestCase
{
    public function testCreateByRulesNameValid()
    {
        /** @var GameFactory $factory */
        $factory = $this->getContainer()->get('GameFactory');

        $game = $factory->createByRulesName('basic');
        self::assertEquals('basic', $game->rules->name);
        self::assertEquals('basic', $game->rules->family);
        self::assertEquals(2, $game->rules->countMaxPlayers);
        self::assertEquals(7, $game->rules->countTilesWhenStartDefault);
        self::assertNull($game->rules->countTilesWhenByPlayers);
        self::assertTrue($game->rules->isFirstMoveRandom);

        $game = $factory->createByRulesName('traditional');
        self::assertEquals('traditional', $game->rules->name);
        self::assertEquals('traditional', $game->rules->family);
        self::assertEquals(4, $game->rules->countMaxPlayers);
        self::assertEquals(7, $game->rules->countTilesWhenStartDefault);
        self::assertEquals([7, 5, 5], $game->rules->countTilesWhenByPlayers);
        self::assertFalse($game->rules->isFirstMoveRandom);

        $game = $factory->createByRulesName('kozel');
        self::assertEquals('kozel', $game->rules->name);
        self::assertEquals('kozel', $game->rules->family);
        self::assertEquals(4, $game->rules->countMaxPlayers);
        self::assertEquals(7, $game->rules->countTilesWhenStartDefault);
        self::assertNull($game->rules->countTilesWhenByPlayers);
        self::assertFalse($game->rules->isFirstMoveRandom);
    }

    public function testCreateByRulesNameNotValid()
    {
        $game = $this->getContainer()->get('GameFactory')->createByRulesName('tiesto');
        self::assertNull($game);
    }

    public function testCreateByMatchIdOk()
    {
        $game        = $this->getContainer()->get('GameFactory')->createByRulesName('basic');
        $matchResult = $game->startNewMatch("Tiesto", 0);
        /** @var Match $matchCreated */
        $matchCreated = $matchResult->getObject();

        //$result = $this->getContainer()->get('GameFactory')->createByMatchId($matchCreated->id, $matchCreated->players[0]->id, $matchCreated->players[0]->secret);
        $result = $this->getContainer()->get('GameFactory')->createByMatchId($matchCreated->id, '', '');
        self::assertFalse($result->hasError());
        self::assertTrue($result->getObject() instanceof Game);
        self::assertJsonStringEqualsJsonString(
            $this->getContainer()->get('Serializer')->serialize($matchCreated),
            $this->getContainer()->get('Serializer')->serialize($result->getObject()->getMatch())
        );
    }

    public function testCreateByMatchIdNotFound()
    {
        $result = $this->getContainer()->get('GameFactory')->createByMatchId("not_exists", "", "");
        self::assertTrue($result->hasError());
        self::assertEquals("Match not found", $result->getError());
    }

    /*public function testCreateByMatchIdPlayerWrongSecret()
    {
        $game = $this->getContainer()->get('GameFactory')->createByRulesName('basic');
        $matchResult = $game->startNewMatch("Tiesto");

        $result = $this->getContainer()->get('GameFactory')->createByMatchId($matchResult->getObject()->id, '', '');
        self::assertTrue($result->hasError());
        self::assertEquals("Match not found", $result->getError());
    }*/
}
