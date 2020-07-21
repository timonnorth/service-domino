<?php

declare(strict_types=1);

namespace Transformer\Resource;

/**
 * Class Tiles
 *
 * @property \ValueObject\Tiles $object
 */
class Tiles extends ResourceAbstract
{
    use TileTrait;

    /** @var bool */
    protected $showList;

    public static function create(\ValueObject\Tiles $tiles): Tiles
    {
        return new Tiles($tiles);
    }

    public function toArray(): array
    {
        $res = [
            'count' => $this->object->count()
        ];
        if ($this->showList) {
            $list = [];
            foreach ($this->object->list as $tile) {
                $list[] = $this->serializeTile($tile);
            }
            $res['list'] = $list;
        }
        return $res;
    }

    public function withList(): Tiles
    {
        $this->showList = true;
        return $this;
    }
}
