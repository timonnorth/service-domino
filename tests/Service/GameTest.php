<?php

declare(strict_types=1);

namespace Tests\Service;

use Entity\Match;
use Service\Game;
use Service\GameFactory;
use Tests\TestCase;

class GameTest extends TestCase
{
    public function testStartNewMatchOk()
    {
        /** @var GameFactory $factory */
        $factory     = $this->getContainer()->get('GameFactory');
        $game        = $factory->createByRulesName('basic');
        $matchResult = $game->startNewMatch("Tiesto");

        self::assertFalse($matchResult->hasError());
        /** @var Match $match */
        $match = $matchResult->getObject();

        self::assertTrue($match->id != '');
        self::assertTrue($match->lastUpdatedHash != '');
        self::assertTrue($match->createdAt instanceof \DateTime);
        self::assertEquals("basic", $match->rules);
        self::assertEquals(Match::STATUS_NEW, $match->status);

        self::assertEquals(1, count($match->players));
        self::assertTrue($match->players[0]->id != '');
        self::assertEquals("Tiesto", $match->players[0]->name);
        self::assertTrue($match->players[0]->secret != '');
        self::assertFalse($match->players[0]->marker);
        self::assertEquals(0, count($match->players[0]->tiles));

        self::assertEquals(28, $match->stock->count());

        self::assertIsArray($match->events);
        self::assertEquals(0, count($match->events));
    }

    public function testStartNewMatchRulesUndefined()
    {
        $game        = new Game();
        $matchResult = $game->startNewMatch("Tiesto");
        self::assertTrue($matchResult->hasError());
        self::assertTrue($matchResult->isSystemError());
        self::assertEquals('Rules undefined to start new game', $matchResult->getError());
    }

    public function testStartNewMatchBadPlayerName()
    {
        /** @var GameFactory $factory */
        $factory     = $this->getContainer()->get('GameFactory');
        $game        = $factory->createByRulesName('basic');
        $matchResult = $game->startNewMatch("t");
        self::assertTrue($matchResult->hasError());
        self::assertFalse($matchResult->isSystemError());
        self::assertEquals('Name should be minimum 2 character', $matchResult->getError());
    }
}
