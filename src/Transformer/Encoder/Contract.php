<?php

declare(strict_types=1);

namespace Transformer\Encoder;

interface Contract
{
    /**
     * @param mixed $data
     * @throws Exception
     * @return string
     */
    public function encode($data): string;

    /**
     * @param string $value
     * @throws Exception
     * @return mixed
     */
    public function decode(string $value);
}
