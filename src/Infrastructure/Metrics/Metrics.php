<?php

declare(strict_types=1);

namespace Infrastructure\Metrics;

/**
 * Class Metrics
 * Should implement or use some Metrics Engine, like Prometheus.
 *
 * @todo Implement engine.
 */
class Metrics
{
    public function counter(string $name, int $count = 1): void
    {
        // Send counter.
    }

    public function gauge(string $name, float $value): void
    {
        // Send gauge.
    }
}
