<?php

declare(strict_types=1);

namespace Controller\JsonRpc;

class ArgumentException extends \Datto\JsonRpc\Exceptions\ArgumentException
{
    public function __construct(string $message = '')
    {
        parent::__construct();

        if ($message != '') {
            $this->message = $message;
        }
    }
}
