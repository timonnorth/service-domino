<?php

declare(strict_types=1);

namespace Transformer\Resource;

use Transformer\Arrayable;

abstract class ResourceAbstract implements Arrayable
{
    /** @var mixed */
    protected $object;

    public function __construct($object)
    {
        $this->setObject($object);
    }

    public function setObject($object): ResourceAbstract
    {
        $this->object = $object;

        return $this;
    }
}
