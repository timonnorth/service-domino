<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Logger;

use Tests\TestCase;

class StubLoggerTest extends TestCase
{
    public function testFast()
    {
        $logger = new \Infrastructure\Logger\StubLogger();
        $logger->emergency('emergency');
        $logger->alert('alert');
        $logger->critical('critical');
        $logger->error('error');
        $logger->warning('warning');
        $logger->notice('notice');
        $logger->info('info');
        $logger->debug('debug');
        $logger->log(3, 'log');
        self::assertTrue(true);
        // What can we do more here in this stub?
    }
}
