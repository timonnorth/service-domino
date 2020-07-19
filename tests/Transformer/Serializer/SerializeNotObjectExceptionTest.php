<?php

declare(strict_types=1);

namespace Tests\Transformer\Serializer;

use Transformer\Serializer\SerializeNotObjectException;

class SerializeNotObjectExceptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test message created.
     */
    public function testAutoMessage()
    {
        self::assertEquals('Can not serialize not object', (new SerializeNotObjectException())->getMessage());
    }

    /**
     * Test message passed.
     */
    public function testManualMessage()
    {
        self::assertEquals('Tiesto', (new SerializeNotObjectException('Tiesto'))->getMessage());
    }
}
