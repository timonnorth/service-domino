<?php

declare(strict_types=1);

namespace Transformer\Serializer;

use Throwable;

class SerializeNotObjectException extends Exception
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
            $message = 'Can not serialize not object';
        }
        parent::__construct($message, $code, $previous);
    }
}
