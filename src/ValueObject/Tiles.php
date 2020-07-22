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
     */
    public function pop(int $count = 1): array
    {
        $list = [];

        for ($i = 0; $i < $count; $i++) {
            $key    = array_rand($this->list);
            $list[] = $this->list[$key];
            unset($this->list[$key]);
        }

        return $list;
    }

    public function has(Tile $tile): bool
    {
        foreach ($this->list as $item) {
            if ($item->isEqual($tile)) {
                $res = true;

                break;
            }
        }

        return $res ?? false;
    }

    /**
     * Find and remove Tile. Returns true if was found.
     */
    public function remove(Tile $tile): bool
    {
        foreach ($this->list as $key => $item) {
            if ($item->isEqual($tile)) {
                unset($this->list[$key]);
                $res = true;

                break;
            }
        }

        return $res ?? false;
    }
}
