<?php

declare(strict_types=1);

namespace Tests\Controller\JsonRpc;

use Controller\JsonRpc\Api;
use Tests\TestCase;

class ApiTest extends TestCase
{
    public function testAdd()
    {
        $api = new Api($this->getContainer());
        $res = $api->evaluate("new-match", ['rules' => 'basic', 'name' => 'Tiesto', 'players' => 2]);
        self::assertIsArray($res);
        self::assertEquals('Tiesto', $res['name']);
    }
}
