<?php

declare(strict_types=1);

namespace Infrastructure\Logger;

use Psr\Log\LoggerInterface;

/**
 * Can be Monolog instead.
 */
class StubLogger implements LoggerInterface
{
    /**
     * System is unusable.
     *
     * @param string  $message
     * @param mixed[] $context
     */
    public function emergency($message, array $context = [])
    {
        // Do nothing.
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string  $message
     * @param mixed[] $context
     */
    public function alert($message, array $context = [])
    {
        // Do nothing.
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string  $message
     * @param mixed[] $context
     */
    public function critical($message, array $context = [])
    {
        // Do nothing.
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string  $message
     * @param mixed[] $context
     */
    public function error($message, array $context = [])
    {
        // Do nothing.
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string  $message
     * @param mixed[] $context
     */
    public function warning($message, array $context = [])
    {
        // Do nothing.
    }

    /**
     * Normal but significant events.
     *
     * @param string  $message
     * @param mixed[] $context
     */
    public function notice($message, array $context = [])
    {
        // Do nothing.
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string  $message
     * @param mixed[] $context
     */
    public function info($message, array $context = [])
    {
        // Do nothing.
    }

    /**
     * Detailed debug information.
     *
     * @param string  $message
     * @param mixed[] $context
     */
    public function debug($message, array $context = [])
    {
        // Do nothing.
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param string  $message
     * @param mixed[] $context
     *
     * @throws \Psr\Log\InvalidArgumentException
     */
    public function log($level, $message, array $context = [])
    {
        // Do nothing.
    }
}
