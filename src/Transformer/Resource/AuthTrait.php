<?php

declare(strict_types=1);

namespace Transformer\Resource;

trait AuthTrait
{
    /**
     * Auth playerId.
     *
     * @var string
     */
    private $playerId;

    public function setPlayerId(string $playerId): ResourceAbstract
    {
        $this->playerId = $playerId;
        return $this;
    }

    public function getPlayerId(): ?string
    {
        return $this->playerId;
    }
}
