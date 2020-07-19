<?php

declare(strict_types=1);

namespace Entity;

use ValueObject\Event;
use ValueObject\Stock;

class Match
{
    /** @var string */
    public $id;
    /** @var string */
    public $secret;
    /** @var string */
    public $lastUpdatedHash;
    /** @var \DateTime */
    public $createdAt;
    /** @var string */
    public $family;
    /** @var string */
    public $rules;
    /** @var string */
    public $status;
    /** @var Player[] */
    public $players;
    /** @var Stock */
    public $stock;
    /** @var Event[] */
    public $events;
}
