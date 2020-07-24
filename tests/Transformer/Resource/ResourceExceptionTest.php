<?php

declare(strict_types=1);

namespace Tests\Transformer\Serializer;

use Transformer\Resource\ResourceException;

class ResourceExceptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test message created.
     */
    public function testAutoMessage()
    {
        self::assertEquals('Not valid resource', (new ResourceException())->getMessage());
    }

    /**
     * Test message passed.
     */
    public function testManualMessage()
    {
        self::assertEquals('Tiesto', (new ResourceException('Tiesto'))->getMessage());
    }
}
