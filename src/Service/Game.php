<?php

declare(strict_types=1);

namespace Service;

use Entity\Match;
use Entity\Player;
use Service\Game\Exception;
use ValueObject\Result;
use ValueObject\Rules;

class Game
{
    /** @var Rules */
    public $rules;

    /**
     * Creating and starting new Match with one main player.
     *
     * @return Result Match
     */
    public function startNewMatch(string $playerName): Result
    {
        try {
            if (!($this->rules instanceof Rules)) {
                throw new Exception("Rules undefined to start new game");
            }
            $playerResult = $this->createPlayer($playerName);
            if ($playerResult->hasError()) {
                $result = $playerResult;
            } else {
                $result = Result::create(Match::create($this->rules, $playerResult->getObject()));
            }
        } catch (\Exception $e) {
            //@todo Log exception.
            $result = Result::create(null, $e->getMessage(), true);
        }
        return $result;
    }

    /**
     * Checks new player name and also that name is not busy by other players.
     *
     * @param string $playerName
     * @param Player[] $existPlayers
     * @return Result Player
     */
    protected function createPlayer(string $playerName, array $existPlayers = []): Result
    {
        $player = Player::create($playerName);
        $validation = $player->selfValidate();
        if ($validation !== null) {
            $result = Result::create(null, $validation);
        } else {
            $result = Result::create($player);
            foreach ($existPlayers as $existPlayer) {
                if ($player->name == $existPlayer->name) {
                    $result = Result::create(
                        null,
                        sprintf(gettext('Another player has already used name "%s"'), $player->name)
                    );
                    break;
                }
            }
        }
        return $result;
    }
}
