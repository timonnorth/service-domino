<?php

declare(strict_types=1);

namespace Tests\Transformer\Encoder;

use Transformer\Encoder\Exception;

class ExceptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test message created.
     */
    public function testAutoMessage()
    {
        $this->assertEquals('Can not encode/decode JSON', (new Exception())->getMessage());
    }

    /**
     * Test message passed.
     */
    public function testManualMessage()
    {
        $this->assertEquals('Tiesto', (new Exception('Tiesto'))->getMessage());
    }
}
