<?php

declare(strict_types=1);

namespace Transformer\Resource;

/**
 * Class Stock
 *
 * @property \ValueObject\Stock $object
 */
class Stock extends ResourceAbstract
{
    use AuthTrait;

    public static function create(\ValueObject\Stock $stock): Stock
    {
        return new Stock($stock);
    }

    public function toArray(): array
    {
        return [
            // It never shows list (not Authed).
            'tiles' => Tiles::create($this->object->tiles)->toArray(),
        ];
    }
}
