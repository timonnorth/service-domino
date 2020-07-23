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
