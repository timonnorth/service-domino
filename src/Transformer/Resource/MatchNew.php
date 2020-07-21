<?php

declare(strict_types=1);

namespace Transformer\Resource;

/**
 * Class MatchNew
 *
 * @property \Entity\Match $object
 */
class MatchNew extends ResourceAbstract
{
    public static function create(\Entity\Match $match): MatchNew
    {
        return new MatchNew($match);
    }

    public function toArray(): array
    {
        // Should find last registered player.
        $player = null;
        for ($i = count($this->object->players) - 1; $i >= 0; $i--) {
            if (!empty($this->object->players[$i]->id)) {
                $player = PlayerMain::create($this->object->players[$i])->toArray();
                break;
            }
        }

        return [
            'id'     => $this->object->id,
            'createdAt' => $this->object->createdAt,
            'status' => $this->object->status,
            'player' => $player
        ];
    }
}
