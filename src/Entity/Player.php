<?php

declare(strict_types=1);

namespace Entity;

use ValueObject\Tiles;

class Player
{
    /** @var string */
    public $id;
    /** @var string */
    public $secret;
    /** @var string */
    public $name;
    /** @var bool */
    public $marker;
    /** @var Tiles */
    public $tiles;
}
