<?php

declare(strict_types=1);

namespace Tests\Controller\JsonRpc;

use Controller\JsonRpc\Api;
use Controller\JsonRpc\ArgumentException;
use Tests\TestCase;

class ApiTest extends TestCase
{
    /** @var string */
    protected $gameId;
    /** @var string */
    protected $playerId;
    /** @var string */
    protected $playerSecret;

    public function testCreateGameAndGetMatch()
    {
        $api = new Api($this->getContainer());
        $res = $api->evaluate("new-match", ['rules' => 'basic', 'name' => 'Tiesto', 'players' => 2]);
        self::assertIsArray($res);
        self::assertTrue($res['id'] != '');
        self::assertEquals('Tiesto', $res['player']['name']);

        $api->evaluate("get-match", ['gameId' => $res['id'], 'playerId' => $res['player']['id'], 'playerSecret' => $res['player']['secret']]);

        $res = $api->evaluate("register-player", ['gameId' => $res['id'], 'name' => 'Bob']);
        self::assertEquals("Bob", $res['player']['name']);

        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('Tile is not valid, should be in format from "0:0" to "6:6"');
        $api->evaluate("play", ['gameId' => $res['id'], 'playerId' => $res['player']['id'], 'playerSecret' => $res['player']['secret'], 'tile' => '7:7', 'position' => 'right']);
    }

    public function testRegisterMatchValidation()
    {
        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('Match not found');
        (new Api($this->getContainer()))->evaluate("register-player", ['gameId' => 'asd', 'name' => 'Bob']);
    }

    public function testGetRules()
    {
        $api = new Api($this->getContainer());
        $res = $api->evaluate("rules", []);
        self::assertIsArray($res);
        self::assertEquals(['basic', 'kozel', 'traditional'], $res);
    }

    public function testGetMatchValidation()
    {
        $api = new Api($this->getContainer());
        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('Param "gameId" is undefined');
        $api->evaluate("get-match", []);
    }
}
