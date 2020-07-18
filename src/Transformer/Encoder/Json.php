<?php

declare(strict_types=1);

namespace Transformer\Encoder;

class Json implements Contract
{
    /**
     * @param mixed $data
     * @throws Exception
     * @return string
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
     * @param string $value
     * @throws Exception
     * @return mixed
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
