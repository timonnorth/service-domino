<?php

declare(strict_types=1);

namespace Tests;

use DI\Container;
use DI\ContainerBuilder;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var Container */
    protected $container;

    protected function getContainer(): Container
    {
        if ($this->container === null) {
            $containerBuilder = new ContainerBuilder();
            $containerBuilder->addDefinitions(__DIR__ . '/config.php');
            $this->container = $containerBuilder->build();
        }
        return $this->container;
    }
}
