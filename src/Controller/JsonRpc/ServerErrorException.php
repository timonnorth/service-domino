<?php

declare(strict_types=1);

namespace Controller\JsonRpc;

class ServerErrorException extends \Datto\JsonRpc\Exceptions\Exception
{
    public const SERVER_ERROR = -32700;

    public function __construct()
    {
        parent::__construct('Server error', self::SERVER_ERROR);
    }
}
