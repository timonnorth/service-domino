<?php

declare(strict_types=1);

namespace ValueObject;

class Event
{
    /** @var string */
    public $type;
    /** @var \DateTime */
    public $createdAt;
    /** @var mixed */
    public $data;
    /** @var string */
    public $playerId;
}
