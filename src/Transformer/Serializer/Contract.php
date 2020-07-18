<?php

declare(strict_types=1);

namespace Transformer\Serializer;

interface Contract
{
    public function serialize($data): string;

    public function deserialize(string $value, string $classname): \stdClass;
}
