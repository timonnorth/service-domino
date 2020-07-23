<?php

declare(strict_types=1);

namespace Transformer\Encoder;

interface EncoderInterface
{
    /**
     * @throws Exception
     */
    public function encode($data): string;

    /**
     * @throws Exception
     */
    public function decode(string $value);
}
