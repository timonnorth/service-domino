<?php

declare(strict_types=1);

namespace Transformer\Resource;

class ResourceException extends \Exception
{
    public function __construct($message = "", $code = 0, ?\Throwable $previous = null)
    {
        if ($message == '') {
            $message = 'Not valid resource';
        }
        parent::__construct($message, $code, $previous);
    }
}
