<?php

declare(strict_types=1);

namespace Infrastructure\Metrics;

class MetricsNames
{
    // Controllers.
    public const JSON_API_REQUEST_ERROR = 'jsonapi-request-errror';
    public const JSON_API_SERVER_EXCEPTION = 'jsonapi-server-exception';
    public const JSON_API_SERVER_ERROR = 'jsonapi-server-error';
    public const JSON_API_SERIALIZING_ERROR = 'jsonapi-serializing-error';
    public const JSON_API_RESPONSE_OK = 'jsonapi-response-ok';

    // Game.
    public const GAME_CREATE_VALIDATION = 'game-create-validation';
    public const GAME_CREATED_OK = 'game-created-ok';
    public const GAME_PLAYERS_IN_MATCH = 'game-players-in-match';
    public const GAME_GET_ERROR = 'game-get-error';
    public const GAME_GET_PROBLEM = 'game-get-problem';
    public const GAME_GET_OK = 'game-get-ok';
    public const GAME_REGISTER_PLAYER_ERROR = 'game-register-player-error';
    public const GAME_REGISTER_PLAYER_PROBLEM = 'game-register-player-problem';
    public const GAME_REGISTER_PLAYER_OK = 'game-register-player-ok';
    public const GAME_PLAY_ERROR = 'game-register-player-error';
    public const GAME_PLAY_PROBLEM = 'game-register-player-problem';
    public const GAME_PLAY_OK = 'game-register-player-ok';
    public const GAME_FINISHED_MATCH = 'game-finished-match';
    public const GAME_FINISHED_MATCH_FISH = 'game-finished-match-fish';
}
