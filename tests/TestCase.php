<?php

declare(strict_types=1);

namespace Tests;

use DI\Container;
use DI\ContainerBuilder;
use Infrastructure\Persistence\File\FileMatchRepository;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\SemaphoreStore;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var Container */
    protected $container;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        if (!defined('__APPDIR__')) {
            define('__APPDIR__', realpath(sprintf('%s/..', __DIR__)));
        }
        //define('APP_MATCH_STORAGE', 'filesystem');
        //define('APP_MATCH_STORAGE_DIR', __APPDIR__ . '/tests/tmp/filestorage');

        parent::__construct($name, $data, $dataName);
    }

    protected function getContainer(): Container
    {
        if ($this->container === null) {
            $containerBuilder = new ContainerBuilder();
            $containerBuilder->addDefinitions(__DIR__ . '/config.php');
            $this->container = $containerBuilder->build();

            $store   = new SemaphoreStore();
            $factory = new LockFactory($store);
            $this->container->set('Locker', $factory);

            // Force set File storage to manager.
            $this->container->get('MatchStorage')->setRepository(
                new FileMatchRepository(
                    $this->container->get('Serializer'),
                    __APPDIR__ . '/tests/tmp/filestorage'
                )
            );
        }

        return $this->container;
    }
}
