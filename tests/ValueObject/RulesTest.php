<?php

declare(strict_types=1);

namespace Tests\ValueObject\Rules;

use ValueObject\Rules;

class RulesTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateByParams()
    {
        $rules = Rules::createByParameters([
            'name'                   => 'tiesto',
            'family'                 => 'test_fam',
            'count_max_players'      => 3,
            'count_tiles_when_start' => 6,
            'count_tiles_by_players' => [7, 5, 6],
            'is_first_move_random'   => true,
        ]);
        self::assertEquals('tiesto', $rules->name);
        self::assertEquals('test_fam', $rules->family);
        self::assertEquals(3, $rules->countMaxPlayers);
        self::assertEquals(6, $rules->countTilesWhenStartDefault);
        self::assertEquals([7, 5, 6], $rules->countTilesWhenByPlayers);
        self::assertTrue($rules->isFirstMoveRandom);
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
    }
}
