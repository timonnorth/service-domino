<?php

declare(strict_types=1);

namespace Tests\Controller\JsonRpc;

use Controller\JsonRpc\ServerErrorException;

class ServerErrorExceptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test message created.
     */
    public function testAutoMessage()
    {
        self::assertEquals('Server error', (new ServerErrorException())->getMessage());
    }
}
