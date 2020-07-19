<?php

declare(strict_types=1);

namespace Tests\Service\Storage;

use Entity\Match;
use Ramsey\Uuid\Uuid;
use Service\Storage\Contract;
use Tests\TestCase;

class FileTest extends TestCase
{
    public function testSetGetMatchOk()
    {
        /** @var Contract $storage */
        $storage = $this->getContainer()->get('Storage');

        $match         = new Match();
        $match->id     = Uuid::uuid4()->toString();
        $match->family = "basicTest";

        $storage->setMatch($match);

        $match2 = $storage->getMatch($match->id);
        self::assertEquals($match->id, $match2->id);
        self::assertEquals('basicTest', $match2->family);
    }
}
