<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Redis;

use Entity\Match;
use Infrastructure\Persistence\Redis\RedisMatchRepository;
use Ramsey\Uuid\Uuid;
use Service\Repository\MatchRepositoryInterface;
use Tests\TestCase;

class RedisMatchRepositoryTest extends TestCase
{
    public function testSetGetMatchOk()
    {
        $factory          = new \M6Web\Component\RedisMock\RedisMockFactory();
        $myRedisMockClass = $factory->getAdapterClass('\Predis\Client');
        $myRedisMock      = new $myRedisMockClass([]);

        /** @var MatchRepositoryInterface $storage */
        $storage = new RedisMatchRepository($this->getContainer()->get('Serializer'), $myRedisMock, 'test');

        $match        = new Match();
        $match->id    = Uuid::uuid4()->toString();
        $match->rules = "basicTest";

        $storage->save($match);

        $match2 = $storage->load($match->id);
        self::assertEquals($match->id, $match2->id);
        self::assertEquals('basicTest', $match2->rules);
    }
}
