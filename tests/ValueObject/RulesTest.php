<?php

declare(strict_types=1);

namespace Tests\ValueObject;

use Service\Family\FamilyBasic;
use Service\Family\FamilyTraditional;
use ValueObject\Rules;

class RulesTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateByParams()
    {
        $rules = Rules::createByParameters([
            'name'                   => 'tiesto',
            'family'                 => 'basic',
            'count_max_players'      => 3,
            'count_tiles_when_start' => 6,
            'count_tiles_by_players' => [9, 3],
            'is_first_move_random'   => true,
        ]);
        self::assertEquals('tiesto', $rules->name);
        self::assertEquals('basic', $rules->family);
        self::assertTrue($rules->getFamily() instanceof FamilyBasic);
        self::assertEquals(3, $rules->countMaxPlayers);
        self::assertEquals(6, $rules->countTilesWhenStartDefault);
        self::assertEquals([9, 3], $rules->countTilesWhenByPlayers);
        self::assertTrue($rules->isFirstMoveRandom);

        self::assertEquals(9, $rules->getCountTilesWhenStart(2));
        self::assertEquals(3, $rules->getCountTilesWhenStart(3));
        self::assertEquals(6, $rules->getCountTilesWhenStart(4));
    }

    public function testUndefinedFamily()
    {
        $rules = Rules::createByParameters([
            'family' => 'test_fam',
        ]);
        self::assertEquals('traditional', $rules->family);
        self::assertTrue($rules->getFamily() instanceof FamilyTraditional);
    }

    public function testCreateDefault()
    {
        $rules = Rules::createByParameters([]);
        self::assertEquals('default', $rules->name);
        self::assertEquals('traditional', $rules->family);
        self::assertEquals(2, $rules->countMaxPlayers);
        self::assertEquals(7, $rules->countTilesWhenStartDefault);
        self::assertNull($rules->countTilesWhenByPlayers);
        self::assertFalse($rules->isFirstMoveRandom);

        self::assertEquals(7, $rules->getCountTilesWhenStart(2));
        self::assertEquals(7, $rules->getCountTilesWhenStart(3));
    }
}
