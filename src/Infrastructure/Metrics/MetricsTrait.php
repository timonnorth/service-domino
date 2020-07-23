<?php

declare(strict_types=1);

namespace Infrastructure\Metrics;

use ValueObject\Result;

trait MetricsTrait
{
    public function getMetricsNameByResult(Result $result, string $nameOk, string $nameError, string $nameSystemError): string
    {
        if ($result->hasError()) {
            $metricsName = $result->isSystemError() ? $nameSystemError : $nameError;
        } else {
            $metricsName = $nameOk;
        }
        return $metricsName;
    }
}
