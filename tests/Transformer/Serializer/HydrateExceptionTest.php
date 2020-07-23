<?php

declare(strict_types=1);

namespace Tests\Transformer\Serializer;

use Transformer\Serializer\HydrateException;

class HydrateExceptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test message created.
     */
    public function testAutoMessage()
    {
        self::assertEquals('Can not hydrate object', (new HydrateException())->getMessage());
    }

    /**
     * Test message passed.
     */
    public function testManualMessage()
    {
        self::assertEquals('Tiesto', (new HydrateException('Tiesto'))->getMessage());
    }
}
