<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Persistence;

use Infrastructure\Persistence\File\FileMatchRepository;
use Infrastructure\Persistence\MatchRepositoryManager;
use Service\Repository\MatchRepositoryInterface;
use Tests\TestCase;

class MatchRepositoryManagerTest extends TestCase
{
    public function testOk()
    {
        $manager = new MatchRepositoryManager($this->getContainer()->get('Serializer'));
        $repo    = $manager->getRepository();
        self::assertTrue($repo instanceof MatchRepositoryInterface);
        //self::assertTrue($repo instanceof FileMatchRepository);
    }
}
