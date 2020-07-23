<?php

declare(strict_types=1);

namespace Tests\Service;

use Service\Game;
use Service\GameFactory;
use Tests\TestCase;
use Transformer\Encoder\Json;
use Transformer\Serializer;
use ValueObject\Event;
use ValueObject\Event\DataPlay;
use ValueObject\Tile;

class GameBigMatchTest extends TestCase
{
    public function testMatchOne()
    {
        $game = $this->startNewMatch();
        /** @var Json $encoder */
        $encoder = $this->getContainer()->get('Json');
        /** @var Serializer $serializer */
        $serializer = $this->getContainer()->get('Serializer');

        $players                              = $game->getMatch()->players;
        $players[0]->marker                   = false;
        $players[1]->marker                   = true;
        $players[2]->marker                   = false;
        $players[3]->marker                   = false;
        $game->getMatch()->events             = [];
        $game->getMatch()->stock->tiles->list = [];
        // Draw new tiles.
        $players[0]->tiles->list = [Tile::create(0, 0), Tile::create(1, 2)];
        $players[1]->tiles->list = [Tile::create(2, 2), Tile::create(2, 3)];
        $players[2]->tiles->list = [Tile::create(3, 3), Tile::create(3, 4)];
        $players[3]->tiles->list = [Tile::create(4, 4), Tile::create(4, 5)];
        // Playing.
        $game->rules->getFamily()->firstStep($game->rules, $game->getMatch());
        $game->getMatch()->moveMarker();
        $game->autoPlay();
        $result = $game->play(Tile::create(1, 2), 'left', $players[0]->id);
        self::assertFalse($result->hasError());
        $game->autoPlay();
        $result = $game->play(Tile::create(2, 3), 'right', $players[1]->id);
        self::assertFalse($result->hasError());
        $game->autoPlay();

        // Win.
        $events = $game->getMatch()->events;
        self::assertEquals(6, count($events));
        // Event 0.
        $tile              = Tile::create(2, 2);
        $tile->orientation = $events[0]->data->tile->orientation; // We do not control orientation of first Tile.
        $event             = Event::create('play', DataPlay::create($tile, null, 'root'), $players[1]->id);
        $event->createdAt  = $events[0]->createdAt;
        self::assertJsonStringEqualsJsonString($serializer->serialize($event), $serializer->serialize($events[0]));
        $tileFirst = $tile;
        // Event 1.
        $event            = Event::create('skip', null, $players[2]->id);
        $event->createdAt = $events[1]->createdAt;
        self::assertJsonStringEqualsJsonString($serializer->serialize($event), $serializer->serialize($events[1]));
        // Event 2.
        $event            = Event::create('skip', null, $players[3]->id);
        $event->createdAt = $events[2]->createdAt;
        self::assertJsonStringEqualsJsonString($serializer->serialize($event), $serializer->serialize($events[2]));
        // Event 3.
        $tileParent        = $tile;
        $tile              = Tile::create(1, 2);
        $tile->orientation = 0;
        $event             = Event::create('play', DataPlay::create($tile, $tileParent, 'left'), $players[0]->id);
        $event->createdAt  = $events[3]->createdAt;
        self::assertJsonStringEqualsJsonString($serializer->serialize($event), $serializer->serialize($events[3]));
        // Event 4.
        $tileParent        = $tileFirst;
        $tile              = Tile::create(2, 3);
        $tile->orientation = 0;
        $event             = Event::create('play', DataPlay::create($tile, $tileParent, 'right'), $players[1]->id);
        $event->createdAt  = $events[4]->createdAt;
        self::assertJsonStringEqualsJsonString($serializer->serialize($event), $serializer->serialize($events[4]));
        // Event 5.
        $event            = Event::create('win', Event\DataScore::create(5, (3 + 4) + (3 + 3) + (4 + 5) + (4 + 4) + 25), $players[1]->id);
        $event->createdAt = $events[5]->createdAt;
        self::assertJsonStringEqualsJsonString($serializer->serialize($event), $serializer->serialize($events[5]));
    }

    protected function startNewMatch($rules = 'kozel'): Game
    {
        /** @var GameFactory $factory */
        $factory     = $this->getContainer()->get('GameFactory');
        $game        = $factory->createByRulesName($rules);
        $matchResult = $game->startNewMatch("Tiesto", 4);
        self::assertFalse($matchResult->hasError());

        $result = $game->registerNewPlayer("John Smith");
        self::assertFalse($result->hasError());

        $result = $game->registerNewPlayer("Silent Bob");
        self::assertFalse($result->hasError());

        $result = $game->registerNewPlayer("Jay");
        self::assertFalse($result->hasError());

        return $game;
    }
}
