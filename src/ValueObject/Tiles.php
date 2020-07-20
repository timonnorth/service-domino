<?php

declare(strict_types=1);

namespace ValueObject;

class Tiles implements \Countable
{
    /** @var Tile[] */
    public $list = [];

    public function count(): int
    {
        return count($this->list);
    }

    /**
     * Shuffle all tiles, must be done when game starts.
     */
    public function shuffle(): void
    {
        //array_rand()
    }

    public function push(array $tiles)
    {
        $this->list = array_merge($this->list, $tiles);
    }

    /**
     * Get shuffled tiles.
     *
     * @param int $count
     * @return array
     */
    public function pop(int $count = 1): array
    {
        $list = [];
        for ($i = 0; $i < $count; $i++) {
            $key = array_rand($this->list);
            $list[] = $this->list[$key];
            unset($this->list[$key]);
        }
        return $list;
    }
}
