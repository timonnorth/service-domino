<?php

declare(strict_types=1);

namespace Tests\Transformer\Serializer;

use Transformer\Serializer\Exception;

class ExceptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test message created.
     */
    public function testAutoMessage()
    {
        static::assertEquals('Can not serialize/deserialize object', (new Exception())->getMessage());
    }

    /**
     * Test message passed.
     */
    public function testManualMessage()
    {
        static::assertEquals('Tiesto', (new Exception('Tiesto'))->getMessage());
    }
}
