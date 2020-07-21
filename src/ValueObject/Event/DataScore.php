<?php

declare(strict_types=1);

namespace ValueObject\Event;

class DataScore
{
    /** @var int */
    public $tilesLeft;
    /** @var int */
    public $score;

    public static function create(int $tilesLeft, int $score): DataScore
    {
        $data           = new DataScore();
        $data->tilesLeft = $tilesLeft;
        $data->score = $score;

        return $data;
    }
}
