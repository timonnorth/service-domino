<?php

declare(strict_types=1);

namespace Transformer\Resource\Event;

use Transformer\Resource\ResourceAbstract;

/**
 * Class Skip
 */
class Skip extends ResourceAbstract
{
    public static function create(): Skip
    {
        return new Skip(null);
    }

    public function toArray(): array
    {
        return [];
    }
}
