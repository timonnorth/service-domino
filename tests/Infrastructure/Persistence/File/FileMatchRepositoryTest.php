<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Persistance\File;

use Entity\Match;
use Ramsey\Uuid\Uuid;
use Service\Repository\MatchRepositoryInterface;
use Tests\TestCase;

class FileMatchRepositoryTest extends TestCase
{
    public function testSetGetMatchOk()
    {
        /** @var MatchRepositoryInterface $storage */
        $storage = $this->getContainer()->get('MatchStorage')->getRepository();

        $match        = new Match();
        $match->id    = Uuid::uuid4()->toString();
        $match->rules = "basicTest";

        $storage->save($match);

        $match2 = $storage->load($match->id);
        self::assertEquals($match->id, $match2->id);
        self::assertEquals('basicTest', $match2->rules);
    }
}
