<?php

define('__APPDIR__', realpath(sprintf('%s/..', __DIR__)));

$store = new \Symfony\Component\Lock\Store\RedisStore(new \Predis\Client('tcp://localhost:6379'));
$store = new \Symfony\Component\Lock\Store\RetryTillSaveStore($store);
$factory = new \Symfony\Component\Lock\LockFactory($store);
