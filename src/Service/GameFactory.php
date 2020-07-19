<?php

declare(strict_types=1);

namespace Service;

use Transformer\Encoder\EncoderInterface;
use ValueObject\Rules;

class GameFactory
{
    /** @var EncoderInterface $encoder */
    protected $encoder;

    public function __construct(EncoderInterface $jsonEncoder)
    {
        $this->encoder = $jsonEncoder;
    }

    public function createByRulesName(string $rulesName): ?Game
    {
        $filename = sprintf('%s/resources/rules/%s.json', __APPDIR__, $rulesName);
        if (is_file($filename)) {
            $rulesParams = $this->encoder->decode(file_get_contents($filename));
            $game = new Game();
            $game->rules = Rules::createByParameters($this->encoder->decode(file_get_contents($filename)));
            $game->rules->name = $rulesName;
        } else {
            $game = null;
        }
        return $game;
    }
}
