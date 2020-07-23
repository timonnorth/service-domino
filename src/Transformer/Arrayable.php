<?php

declare(strict_types=1);

namespace Transformer;

interface Arrayable
{
    /**
     * Get the instance as an array.
     */
    public function toArray(): array;
}
