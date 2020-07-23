<?php

declare(strict_types=1);

namespace Transformer\Encoder;

use Throwable;

class Exception extends \Exception
{
    /**
     * Exception constructor.
     *
     * @param string $message
     * @param int    $code
     */
    public function __construct($message = "", $code = 0, ?Throwable $previous = null)
    {
        if ($message == '') {
            $message = 'Can not encode/decode JSON';
        }
        parent::__construct($message, $code, $previous);
    }
}
