<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Datto\JsonRpc\Http\Client;
use Datto\JsonRpc\Responses\ErrorResponse;

/** Count of milliseconds for sleeping between api requests.  */
const STEP_SLEEP_MILLI = 1000;

main();

function main()
{
    if (!isset($_SERVER['argv'][1])) {
        die("Server is not specified. Plz use as: 'php cli/client.php http://127.0.0.1:8080'\n");
    }
    $jsonrpc = new Client($_SERVER['argv'][1]);

    write(getLogo());
    write("Welcome to DOMINO!\n\n");

    while (true) {
        write("Please choose:\n1. New match\n2. Register in existing match\n");
        $i = read();

        if ($i == '1') {
            $credentials = createNewMatch($jsonrpc);
            write(sprintf(
                "\nMatch ID is '%s', send it to your friends to let them join the match.\n\n",
                $credentials['gameId']
            ));
        } elseif ($i == '2') {
            $credentials = registerInMatch($jsonrpc);
        } else {
            continue;
        }

        break;
    }

    // Start the game.
    $status               = '';
    $lastHash             = '';
    $countProceededEvents = '';
    $isMatchUpdated       = false;

    while (true) {
        if (!$isMatchUpdated) {
            $match = getMatch($jsonrpc, $credentials);
        }
        $isMatchUpdated = false;

        if ($match === null) {
            usleep(STEP_SLEEP_MILLI * 1000);

            continue;
        }

        if ($match['status'] == 'new' && $status == '') {
            write("Waiting for another players to join...");
        } elseif ($match['status'] == 'new' || $lastHash == $match['lastUpdatedHash']) {
            // Just wait...
            write('.');
        } else {
            $me = getPlayerById($credentials['playerId'], $match);
            // Show events and status.
            $count = count($match['events']);

            if ($count > $countProceededEvents) {
                if ($countProceededEvents == 0) {
                    // For very first step.
                    write(sprintf("\nGame starting with first tile: <%s>", $match['events'][0]['data']['tile']));
                    $countProceededEvents++;
                }

                for ($i = $countProceededEvents; $i < $count; $i++) {
                    $event = $match['events'][$i];

                    if ($event['type'] == 'play') {
                        $el = sprintf('<%s>', $event['data']['tile']);
                        write(sprintf(
                            "\n%s plays %s to connect to tile %s on the board",
                            getPlayerById($event['playerId'], $match)['name'],
                            sprintf('<%s>', $event['data']['tile']),
                            sprintf('<%s>', $event['data']['parent'])
                        ));
                    } elseif ($event['type'] == 'draw') {
                        if ($event['data'][0] == '') {
                            // For hidden tiles.
                            write(sprintf(
                                "\n%s can't play, drawing %d tile(s)",
                                getPlayerById($event['playerId'], $match)['name'],
                                count($event['data'])
                            ));
                        } else {
                            // For open tiles.
                            $list = '';

                            foreach ($event['data'] as $tile) {
                                $list .= sprintf('<%s>', $tile);
                            }
                            write(sprintf(
                                "\n%s can't play, drawing tile(s): %s",
                                getPlayerById($event['playerId'], $match)['name'],
                                $list
                            ));
                        }
                    } elseif ($event['type'] == 'skip') {
                        write(sprintf(
                            "\n%s can't play and skip his/her move",
                            getPlayerById($event['playerId'], $match)['name']
                        ));
                    }
                }
                $countProceededEvents = $count;
                // Show game status.
                showBoardStatus($me, $match);
            }

            if ($match['status'] == 'finished') {
                // Game finished.
                $event = end($match['events']);
                write(sprintf("\n\nPlayer %s has won!\n", getPlayerById($event['playerId'], $match)['name']));
                write(sprintf("Tiles left: %d, Score: %d\n\n", $event['data']['tilesLeft'], $event['data']['score']));

                break;
            }
            // Playing.
            if ($me['marker']) {
                // My step.
                while (true) {
                    $tile = '';

                    while ($tile == '') {
                        write(sprintf("\nChoose tile (example \"%s\"): ", $me['tiles']['list'][0]));
                        $tile = read();
                        if (strlen($tile) == 2) {
                            // Normalize fast input.
                            $tile = $tile[0] . ':' . $tile[1];
                        }
                    }
                    $position = '';

                    while ($position == '') {
                        write("Input tile position ([l]eft or [r]right): ");
                        $position = read();

                        if ($position == 'l') {
                            $position = 'left';
                        } elseif ($position == 'r') {
                            $position = 'right';
                        }
                    }
                    $match = play($jsonrpc, $credentials, $tile, $position);

                    if ($match) {
                        break;
                    }
                }
                $isMatchUpdated = true;
            } else {
                write(sprintf("\nWaiting while %s plays...", getMarkedPlayer($match)['name']));
            }
        }

        if (!$isMatchUpdated) {
            $status   = $match['status'];
            $lastHash = $match['lastUpdatedHash'];
        }
        usleep(STEP_SLEEP_MILLI * 1000);
    }
}

/**
 * Registers new match and returns credentials.
 */
function createNewMatch(Client $jsonrpc): array
{
    write("\nPlease choose the rules:\n");
    $jsonrpc->query("rules", null, $response);
    sendOrDie($jsonrpc);

    foreach ($response as $key => $value) {
        write(sprintf("%d. %s\n", $key + 1, $value));
    }

    while (true) {
        // Prepare rules.
        write(sprintf("\nInput number from 1 to %s (1): ", count($response)));
        $i = read();

        if ($i == '') {
            $rules = $response[0];

            break;
        }

        if (isset($response[(int)$i - 1])) {
            $rules = $response[(int)$i - 1];

            break;
        }
        write("Not correct value.\n");
    }

    while (true) {
        // Prepare players.
        write("Count of players (2): ");
        $count = (int)read();

        if ($count == '') {
            $count = 2;
        }
        // Prepare name.
        $name = '';

        while ($name == '') {
            write("Your name: ");
            $name = read();
        }

        $jsonrpc->query("new-match", [
            'name'    => $name,
            'players' => $count,
            'rules'   => $rules,
        ], $response);
        sendOrDie($jsonrpc);

        if ($response instanceof ErrorResponse) {
            showError($response);
        } else {
            break;
        }
    }

    return [
        'gameId'       => $response['id'],
        'playerId'     => $response['player']['id'],
        'playerSecret' => $response['player']['secret'],
    ];
}

/**
 * Registers new player in existing match and returns credentials.
 */
function registerInMatch(Client $jsonrpc): array
{
    while (true) {
        $gameId = '';

        while ($gameId == '') {
            write("\nInput game ID to join: ");
            $gameId = read();
        }

        $name = '';

        while ($name == '') {
            write("Your name: ");
            $name = read();
        }

        $params = [
            'gameId' => $gameId,
            'name'   => $name,
        ];
        $jsonrpc->query("register-player", $params, $response);
        sendOrDie($jsonrpc);

        if ($response instanceof ErrorResponse) {
            showError($response);
        } else {
            break;
        }
    }

    return [
        'gameId'       => $response['id'],
        'playerId'     => $response['player']['id'],
        'playerSecret' => $response['player']['secret'],
    ];
}

function getMatch(Client $jsonrpc, array $credentials): ?array
{
    $jsonrpc->query("get-match", $credentials, $match);
    sendOrDie($jsonrpc);

    if ($match instanceof ErrorResponse) {
        showError($match);
        $match = null;
    }

    return $match;
}

function play(Client $jsonrpc, array $credentials, string $tile, string $position): ?array
{
    $params = array_merge($credentials, [
        'tile'     => $tile,
        'position' => $position,
    ]);
    $jsonrpc->query("play", $params, $match);
    sendOrDie($jsonrpc);

    if ($match instanceof ErrorResponse) {
        showError($match);
        $match = null;
    }

    return $match;
}

function getPlayerById(string $playerId, array $match): array
{
    $result = [];

    foreach ($match['players'] as $player) {
        if ($player['id'] == $playerId) {
            $result = $player;

            break;
        }
    }

    return $result;
}

function getMarkedPlayer(array $match): array
{
    foreach ($match['players'] as $player) {
        if ($player['marker']) {
            break;
        }
    }

    return $player;
}

function showBoardStatus(array $me, array $match): void
{
    // Show board.
    if (count($match['events']) > 1) {
        $board = '';

        foreach ($match['events'] as $event) {
            if ($event['type'] == 'play') {
                $el = sprintf('<%s>', $event['data']['tile']);

                if ($event['data']['position'] == 'right') {
                    $board .= $el;
                } else {
                    $board = $el . $board;
                }
            }
        }
        write(sprintf("\nBoard is now: %s", $board));
    }

    // Show tiles and stock.
    if ($match['stock']['tiles']['count'] > 0) {
        write(sprintf("\nStock has %d tiles", $match['stock']['tiles']['count']));
    } else {
        write("\nStock is empty now");
    }

    foreach ($match['players'] as $player) {
        if ($player['id'] != $me['id']) {
            write(sprintf(", %s has %d tiles", $player['name'], $player['tiles']['count']));
        }
    }
    write("\nYour tiles: ");

    foreach ($me['tiles']['list'] as $tile) {
        write(sprintf("<%s>", $tile));
    }
}

function sendOrDie(Client $client)
{
    try {
        $client->send();
    } catch (Exception $e) {
        die(sprintf("Can not send request: %s\n", $e->getMessage()));
    }
}

function showError(ErrorResponse $response)
{
    write(sprintf("Error: %s\n", $response->getMessage()));
}

function write(string $data)
{
    fwrite(STDOUT, $data);
}

function read(): string
{
    while ($f = fgets(STDIN)) {
        return substr($f, 0, -1);
    }
}

function getLogo(): string
{
    return ' _______    ______   __       __  ______  __    __   ______  
|       \  /      \ |  \     /  \|      \|  \  |  \ /      \ 
| $$$$$$$\|  $$$$$$\| $$\   /  $$ \$$$$$$| $$\ | $$|  $$$$$$\
| $$  | $$| $$  | $$| $$$\ /  $$$  | $$  | $$$\| $$| $$  | $$
| $$  | $$| $$  | $$| $$$$\  $$$$  | $$  | $$$$\ $$| $$  | $$
| $$  | $$| $$  | $$| $$\$$ $$ $$  | $$  | $$\$$ $$| $$  | $$
| $$__/ $$| $$__/ $$| $$ \$$$| $$ _| $$_ | $$ \$$$$| $$__/ $$
| $$    $$ \$$    $$| $$  \$ | $$|   $$ \| $$  \$$$ \$$    $$
 \$$$$$$$   \$$$$$$  \$$      \$$ \$$$$$$ \$$   \$$  \$$$$$$ 
                                                             
';
}
