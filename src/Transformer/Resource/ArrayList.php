<?php

declare(strict_types=1);

namespace Transformer\Resource;

/**
 * Class ArrayList
 *
 * @property array $object
 */
class ArrayList extends ResourceAbstract
{
    public static function create(array $list): ArrayList
    {
        return new ArrayList($list);
    }

    public function toArray(): array
    {
        return $this->object;
    }
}
