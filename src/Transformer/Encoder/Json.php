<?php

declare(strict_types=1);

namespace Transformer\Encoder;

class Json implements EncoderInterface
{
    /**
     * @throws Exception
     */
    public function encode($data): string
    {
        $res = json_encode($data);

        if ($res === false) {
            throw new Exception('Can not encode JSON');
        }

        return $res;
    }

    /**
     * @throws Exception
     */
    public function decode(string $value)
    {
        $res = json_decode($value, true);

        if ($res === null) {
            throw new Exception('Can not decode JSON');
        }

        return $res;
    }
}
