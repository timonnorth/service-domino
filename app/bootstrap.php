<?php

use Controller\JsonRpc\Api;
use Datto\JsonRpc\Http\Server;
use DI\ContainerBuilder;

require_once __DIR__ . '/../vendor/autoload.php';

define('__APPDIR__', realpath(sprintf('%s/..', __DIR__)));

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/config.php');
$container = $containerBuilder->build();

// For working with ENV https://github.com/vlucas/phpdotenv can be used in future.
//
$store   = new \Symfony\Component\Lock\Store\RedisStore(new \Predis\Client(getenv('APP_REDIS_PARAMS')));
$store   = new \Symfony\Component\Lock\Store\RetryTillSaveStore($store);
$factory = new \Symfony\Component\Lock\LockFactory($store);
$container->set('Locker', $factory);

// Handle JSONRPC request.
(new Server(new Api($container)))->reply();
