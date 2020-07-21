<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Datto\JsonRpc\Responses\ErrorResponse;

main();

function main()
{
    if (!isset($_SERVER['argv'][1])) {
        die("Server is not specified. Plz use as: 'php cli/client.php http://127.0.0.1:8080'\n");
    }
    $client = new \Datto\JsonRpc\Http\Client($_SERVER['argv'][1]);
    $client->query("rules", null, $response);
    sendOrDie($client);

    write(getLogo());
    write("Welcome to DOMINO! Please choose the rules:\n\n");
    foreach ($response as $key => $value) {
        write(sprintf("%d. %s\n", $key + 1, $value));
    }
    while (true) {
        write(sprintf("\nInput number from 1 to %s (1): ", count($response)));
        $i = read();
        if ($i == '') {
            $rules = $response[0];
            break;
        } elseif (isset($response[(int)$i - 1])) {
            $rules = $response[(int)$i - 1];
            break;
        } else {
            write("Not correct value.\n");
        }
    }
    while (true) {
        write("Count of players (2): ");
        $count = (int)read();
        if ($count == '') {
            $count = 2;
        }
        $name = '';
        while ($name == '') {
            write("Your name: ");
            $name = read();
        }

        $client->query("new-match", [
            'name' => $name,
            'players' => $count,
            'rules' => $rules
        ], $response);
        sendOrDie($client);
        if ($response instanceof ErrorResponse) {
            showError($response);
        } else {
            break;
        }
    }
    var_dump($response);
}

function sendOrDie(\Datto\JsonRpc\Http\Client $client)
{
    try {
        $client->send();
    } catch (Exception $e) {
        die(sprintf("Can not send request: %s\n", $e->getMessage()));
    }
}

function showError(ErrorResponse $response) {
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
