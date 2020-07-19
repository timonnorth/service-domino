<?php

declare(strict_types=1);

namespace Tests\Service;

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
        self::assertEquals('traditional', $game->rules->family);
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
}
