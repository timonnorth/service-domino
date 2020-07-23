<?php

declare(strict_types=1);

namespace Tests\Service;

use Entity\Match;
use Service\Game;
use Service\GameFactory;
use Tests\TestCase;
use ValueObject\Event\DataPlay;
use ValueObject\Tile;

class GameTest extends TestCase
{
    /** @var string */
    protected $matchId;
    /** @var string */
    protected $playerId;
    /** @var string */
    protected $playerSecret;

    public function testStartNewMatchOk()
    {
        /** @var GameFactory $factory */
        $factory     = $this->getContainer()->get('GameFactory');
        $game        = $factory->createByRulesName('basic');
        $matchResult = $game->startNewMatch("Tiesto", 0);

        self::assertFalse($matchResult->hasError());
        /** @var Match $match */
        $match              = $matchResult->getObject();
        $this->matchId      = $match->id;
        $this->playerId     = $match->players[0]->id;
        $this->playerSecret = $match->players[0]->secret;

        self::assertTrue($match->id != '');
        self::assertTrue($match->lastUpdatedHash != '');
        self::assertTrue($match->createdAt >= time() - 3 && $match->createdAt <= time() + 3);
        self::assertEquals("basic", $match->rules);
        self::assertEquals(Match::STATUS_NEW, $match->status);

        self::assertEquals(2, count($match->players));
        self::assertTrue($match->players[0]->id != '');
        self::assertEquals("Tiesto", $match->players[0]->name);
        self::assertTrue($match->players[0]->secret != '');
        self::assertTrue($match->players[0]->marker);
        self::assertEquals(0, count($match->players[0]->tiles));
        self::assertEquals('', $match->players[1]->id);

        self::assertEquals(28, $match->stock->count());

        self::assertIsArray($match->events);
        self::assertEquals(0, count($match->events));
    }

    public function testStartNewMatchRulesUndefined()
    {
        $game = new Game(
            $this->getContainer()->get('Storage'),
            $this->getContainer()->get('Locker'),
            $this->getContainer()->get('Metrics'),
            null
        );
        $matchResult = $game->startNewMatch("Tiesto", 0);
        self::assertTrue($matchResult->hasError());
        self::assertTrue($matchResult->isSystemError());
        self::assertEquals('Rules undefined to start new game', $matchResult->getError());
    }

    public function testStartNewMatchBadPlayerName()
    {
        /** @var GameFactory $factory */
        $factory     = $this->getContainer()->get('GameFactory');
        $game        = $factory->createByRulesName('basic');
        $matchResult = $game->startNewMatch("t", 0);
        self::assertTrue($matchResult->hasError());
        self::assertFalse($matchResult->isSystemError());
        self::assertEquals('Name should be minimum 2 character', $matchResult->getError());
    }

    public function testStartNewMatchBadCountPlayers()
    {
        /** @var GameFactory $factory */
        $factory     = $this->getContainer()->get('GameFactory');
        $game        = $factory->createByRulesName('basic');
        $matchResult = $game->startNewMatch("Tiesto", 3);
        self::assertTrue($matchResult->hasError());
        self::assertFalse($matchResult->isSystemError());
        self::assertEquals('Count of players can not be more than 2', $matchResult->getError());
    }

    public function testRegisterNewPlayerOk()
    {
        $game = $this->startGame();

        $result = $game->registerNewPlayer("John Smith");
        self::assertEquals("John Smith", $game->getMatch()->players[1]->name);
    }

    public function testRegisterNewPlayersDrawFirstMove()
    {
        $factory = $this->getContainer()->get('GameFactory');
        /** @var Game $game */
        $game = $factory->createByRulesName('traditional');
        $game->startNewMatch("Tiesto", 4);
        self::assertTrue($game->getMatch()->players[0]->marker);

        $result = $game->registerNewPlayer("John Smith");
        self::assertFalse($result->hasError());
        self::assertEquals("John Smith", $game->getMatch()->players[1]->name);
        self::assertFalse($game->getMatch()->players[0]->marker);
        self::assertTrue($game->getMatch()->players[1]->marker);

        $result = $game->registerNewPlayer("Bob");
        self::assertFalse($result->hasError());
        self::assertEquals("Bob", $game->getMatch()->players[2]->name);
        self::assertFalse($game->getMatch()->players[0]->marker);
        self::assertFalse($game->getMatch()->players[1]->marker);
        self::assertTrue($game->getMatch()->players[2]->marker);

        $result = $game->registerNewPlayer("Pieter");
        self::assertFalse($result->hasError());
        self::assertEquals("Pieter", $game->getMatch()->players[3]->name);
        // And we do not know where is marker after.

        // No fifth player.
        $result = $game->registerNewPlayer("Cat");
        self::assertTrue($result->hasError());
        self::assertEquals('No free slot to register new player', $result->getError());

        // Check count of tiles.
        $markers = 0;

        for ($i = 0; $i < 4; $i++) {
            if ($i < 3 && $game->getMatch()->players[$i + 1]->marker || $i == 3 && $game->getMatch()->players[0]->marker) {
                $expected = 4;
                $playerId = $game->getMatch()->players[$i]->id;
            } else {
                $expected = 5;
            }
            self::assertEquals($expected, $game->getMatch()->players[$i]->tiles->count());

            if ($game->getMatch()->players[$i]->marker) {
                $markers++;
            }
        }
        self::assertEquals(8, $game->getMatch()->stock->count());
        self::assertEquals(1, $markers);

        // Check first event.
        self::assertEquals(1, count($game->getMatch()->events));
        $event = $game->getMatch()->events[0];
        self::assertEquals("play", $event->type);
        self::assertTrue($event->createdAt >= time() - 3 && $event->createdAt <= time() + 3);
        self::assertTrue($event->data instanceof DataPlay);
        self::assertTrue($event->data->tile instanceof Tile);
        self::assertNull($event->data->parent);
        self::assertEquals("root", $event->data->position);
        self::assertEquals($playerId, $event->playerId);
    }

    public function testRegisterNewPlayerValidationError()
    {
        $game = $this->startGame();

        $result = $game->registerNewPlayer("J");
        self::assertTrue($result->hasError());
        self::assertFalse($result->isSystemError());
        self::assertEquals('Name should be minimum 2 character', $result->getError());
    }

    public function testRegisterNewPlayerDuplicateError()
    {
        $game = $this->startGame();

        $result = $game->registerNewPlayer("Tiesto");
        self::assertTrue($result->hasError());
        self::assertFalse($result->isSystemError());
        self::assertEquals('Another player has already used name "Tiesto"', $result->getError());
    }

    public function testRegisterNewPlayerSlotBusyError()
    {
        $game = $this->startGame();

        $result = $game->registerNewPlayer("John Smith");
        self::assertFalse($result->hasError());

        $result = $game->registerNewPlayer("Bob");
        self::assertTrue($result->hasError());
        self::assertFalse($result->isSystemError());
        self::assertEquals('No free slot to register new player', $result->getError());
    }

    protected function startGame(): Game
    {
        $factory = $this->getContainer()->get('GameFactory');
        /** @var Game $game */
        $game = $factory->createByRulesName('basic');
        $game->startNewMatch("Tiesto", 0);

        return $game;
    }
}
