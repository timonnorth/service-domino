<?php

declare(strict_types=1);

namespace Transformer\Resource\Event;

use Transformer\Resource\ResourceAbstract;
use ValueObject\Event\DataScore;

/**
 * Class Score
 *
 * @property ?DataScore $object
 */
class Score extends ResourceAbstract
{
    public static function create(?DataScore $dataScore): Score
    {
        return new Score($dataScore);
    }

    public function toArray(): array
    {
        $res = [];

        if ($this->object) {
            $res['tilesLeft'] = $this->object->tilesLeft;
            $res['score']     = $this->object->score;
        }

        return $res;
    }
}
