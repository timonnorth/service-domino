<?php

return [
    'Serializer' => \DI\create(\Transformer\Serializer::class)
        ->constructor(new \Transformer\Encoder\Json()),
    'MatchStorage' => \DI\create(\Infrastructure\Persistence\MatchRepositoryManager::class)
        ->constructor(
            \DI\get('Serializer')
        ),
    'GameFactory' => \DI\create(\Service\GameFactory::class)
        ->constructor(
            \DI\get('RulesLoader'),
            \DI\get('MatchStorage'),
            \DI\get('Locker'),
            \DI\get('Metrics'),
            \DI\get('Logger')
        ),
    'Json'        => \DI\create(\Transformer\Encoder\Json::class),
    'RulesLoader' => \DI\create(\Service\RulesLoader::class)
        ->constructor(
            \DI\get('Json'),
            \DI\get('Logger')
        ),
    'Metrics' => \DI\create(\Infrastructure\Metrics\Metrics::class),
    'Logger' => \DI\create(Infrastructure\Logger\StubLogger::class),
];
