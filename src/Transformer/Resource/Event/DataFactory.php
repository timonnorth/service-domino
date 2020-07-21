<?php

declare(strict_types=1);

namespace Transformer\Resource\Event;

use Transformer\Resource\ResourceAbstract;
use Transformer\Resource\ResourceException;
use ValueObject\Event;

class DataFactory
{
    /**
     * @throws ResourceException
     */
    public static function create(string $type, $data): ResourceAbstract
    {
        switch ($type) {
            case Event::TYPE_PLAY:
                $res = Play::create($data);

                break;

            default:
                throw new ResourceException(sprintf('Undefined event type "%s"', $type));

                break;
        }

        return $res;
    }
}
