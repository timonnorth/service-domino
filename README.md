# DOMINO

[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]

## Ideas, developing

 * **Storage**: implements storing in filesystem and redis, configurable with envs (see: docker-compose.override.yml). When filesystem is used - service can not be shared via servers. Tests always use filesystem-storage.
 * **Rules**: some parameters are configurable, you can create a few new versions of Domino-family just creating new json file in /resources/rules. Potentially can be allowed to Players to create and upload own Rules in future.
 * **Family**: Domino-family is a Strategy to realize some variants of large "list of domino games". Game engine will use one strategy (described in Rules) to do some actions like detect who steps first and how to calculate scores. You can see details in /src/Service/Family.
 * **Logging and Metrics**: logging is not implemented (is @todo) because usually it will be either Monolog or some own complex solution (what is preferable). Example of metrics with counters and gauges presents.
 * **Transformers and Hydrators**: simple (arrayable) Resources what can be reused in other protocols (like jsonApi etc). Projects does not have hydrators because client's data is not complex and can be easy decoded/validated with jsonrpc engine. For other protocols some hydrators can be implemented. Project also has own "Serializer" what helps encode/decode entities for storing in some repository.  
 * **Api**: for now I implemented only JSONRPC (main reason - speed of developing), potentially projects can have different endpoints for protocols (see Transformers).
 * [Api endpoints](#jsonrpc-endpoints)
 * **Client**: as it's not very comfortable to play via Postman or other jsonrpc client (but is absolutely possible!) I created very simple [php-client](#client) to play in console mode. It does not have tests and code styling (just large spaghetti in one file) because I do not consider it as part of this assignment, please do not judge it ^_^. It uses plain-php and jsonrpc-client lib. Otherwise it shows how easy is to create client-app when you have good api.

## Libraries

I used a few libs to not invent a "bike":

 * datto/json-rpc: to use jsonrpc protocol for simple api;
 * php-di/php-di: DI container implementation;
 * symfony/lock: simple lock manager;
 * ramsey/uuid: to generate UUID4 identifiers;
 * predis/predis: Redis client, uses in Locks and MatchRepository to save game's data. 

## Install

"Composer require" is not available as project is private. To install it locally, please use git:

``` bash
    git clone https://github.com/timonnorth/service-domino.git
    cd service-domino
    cp docker-compose.override.yml.dist docker-compose.override.yml
    docker-compose up -d
    docker exec -it service-domino bash -c "composer update --optimize-autoloader"
```

Potentially we do not need start "composer update" on prod or test environment. But locally (see docker-compose.override.yml) we connect our local folder with code to have the possibility change code and see results "on the fly". That's why composer should be called again.

## Testing

``` bash
    docker exec -it service-domino bash -c "composer test"
```

## Client

You can start client of game on one or few hosts by command "php cli/client.php HTTP://SERVER". It's checked that it works with [ngrock](https://ngrok.com/) and Players can join the Game worldwide when you start Server side via docker.

``` bash
    docker exec -it service-domino bash -c "php cli/client.php http://host.docker.internal:8080"
```

If you have PHP installed on your host (>=7.1) you can start client locally:

``` bash
    php cli/client.php http://127.0.0.1:8080
```

## JSONRPC endpoints:

1. Show list of rules.
    
    Request:
```json
{
    "jsonrpc": "2.0",
    "id": "test",
    "method": "rules"
}
```
   Response: 
```json
{
    "jsonrpc": "2.0",
    "id": "test",
    "result": [
        "basic",
        "kozel",
        "traditional"
    ]
}
```

2. Create new Match (game).

    Request:
```json
{
    "jsonrpc": "2.0",
    "id": "test",
    "method": "new-match",
    "params": {
        "name": "John Smith",
        "players": 4,
        "rules": "traditional"
    }
}
```
   Response:
```json
{
    "jsonrpc": "2.0",
    "id": "test",
    "result": {
        "id": "76472357-ba00-4619-918f-4cfe20a55ecd",
        "createdAt": 1595526906,
        "status": "new",
        "player": {
            "id": "379df6fd-6229-48d2-92b2-b476eb1f3cb5",
            "name": "John Smith",
            "marker": true,
            "tiles": {
                "count": 0
            },
            "secret": "260d4c02-4680-41a5-a15b-3b5fd32a4285"
        }
    }
}
```

3. Join the Match:

    Request:
```json
{
    "jsonrpc": "2.0",
    "id": "test",
    "method": "register-player",
    "params": {
        "gameId": "76472357-ba00-4619-918f-4cfe20a55ecd",
        "name": "Bobby Smart"
    }
}
```
   Response:
```json
{
    "jsonrpc": "2.0",
    "id": "test",
    "result": {
        "id": "76472357-ba00-4619-918f-4cfe20a55ecd",
        "createdAt": 1595526906,
        "status": "play",
        "player": {
            "id": "f4653965-9f91-4516-9948-d9adfbc43b4e",
            "name": "Bobby Smart",
            "marker": false,
            "tiles": {
                "count": 6
            },
            "secret": "ed80a229-a906-4e1a-84eb-89d0153663b6"
        }
    }
}
```

4. Get Match.

    Request:
```json
{
    "jsonrpc": "2.0",
    "id": "test",
    "method": "get-match",
    "params": {
        "gameId": "76472357-ba00-4619-918f-4cfe20a55ecd",
        "playerId": "379df6fd-6229-48d2-92b2-b476eb1f3cb5",
        "playerSecret": "260d4c02-4680-41a5-a15b-3b5fd32a4285"
    }
}
```
   Response:  
```json
{
    "jsonrpc": "2.0",
    "id": "test",
    "result": {
        "id": "76472357-ba00-4619-918f-4cfe20a55ecd",
        "lastUpdatedHash": "35aa5931-a326-430b-bfa2-0365dd87591f",
        "createdAt": 1595526906,
        "rules": "traditional",
        "status": "play",
        "players": [
            {
                "id": "379df6fd-6229-48d2-92b2-b476eb1f3cb5",
                "name": "John Smith",
                "marker": true,
                "tiles": {
                    "count": 5,
                    "list": [
                        "0:3",
                        "2:6",
                        "2:5",
                        "0:4",
                        "0:1"
                    ]
                }
            },
            {
                "id": "f4653965-9f91-4516-9948-d9adfbc43b4e",
                "name": "Bobby Smart",
                "marker": false,
                "tiles": {
                    "count": 4
                }
            }
        ],
        "stock": {
            "tiles": {
                "count": 14
            }
        },
        "events": [
            {
                "type": "play",
                "createdAt": 1595526943,
                "playerId": "f4653965-9f91-4516-9948-d9adfbc43b4e",
                "data": {
                    "tile": "4:4",
                    "position": "root",
                    "parent": null
                }
            },
            {
                "type": "play",
                "createdAt": 1595527272,
                "playerId": "379df6fd-6229-48d2-92b2-b476eb1f3cb5",
                "data": {
                    "tile": "4:3",
                    "position": "right",
                    "parent": "4:4"
                }
            },
            {
                "type": "play",
                "createdAt": 1595527282,
                "playerId": "f4653965-9f91-4516-9948-d9adfbc43b4e",
                "data": {
                    "tile": "6:4",
                    "position": "left",
                    "parent": "4:4"
                }
            },
            {
                "type": "play",
                "createdAt": 1595527300,
                "playerId": "379df6fd-6229-48d2-92b2-b476eb1f3cb5",
                "data": {
                    "tile": "3:5",
                    "position": "right",
                    "parent": "4:3"
                }
            },
            {
                "type": "play",
                "createdAt": 1595527312,
                "playerId": "f4653965-9f91-4516-9948-d9adfbc43b4e",
                "data": {
                    "tile": "5:6",
                    "position": "right",
                    "parent": "3:5"
                }
            }
        ]
    }
}
```

## Credits

- [Oleksandr Ieremeev][link-author]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/timonnorth/service-domino/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/timonnorth/service-domino.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/timonnorth/service-domino.svg?style=flat-square

[link-travis]: https://travis-ci.org/timonnorth/service-domino?branch=master
[link-scrutinizer]: https://scrutinizer-ci.com/g/timonnorth/service-domino/code-structure?branch=master
[link-code-quality]: https://scrutinizer-ci.com/g/timonnorth/service-domino?branch=master
[link-author]: https://github.com/oitimon
