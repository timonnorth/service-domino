<?php

declare(strict_types=1);

namespace Transformer\Serializer;

interface Contract
{
    /**
     * @param mixed $data
     * @return string
     */
    public function serialize($data): string;

    /**
     * @param string $value
     * @param string $classname
     * @return \stdClass
     */
    public function deserialize(string $value, string $classname): \stdClass;
}
