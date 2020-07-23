<?php

return [
    'Serializer' => \DI\create(\Transformer\Serializer::class)
        ->constructor(new \Transformer\Encoder\Json()),
    'Storage' => \DI\create(\Service\Storage\File::class)
        ->constructor(
            \DI\get('Serializer'),
            sprintf('%s/tmp/filestorage', __DIR__)
        ),
    'GameFactory' => \DI\create(\Service\GameFactory::class)
        ->constructor(
            \DI\get('RulesLoader'),
            \DI\get('Storage'),
            \DI\get('Locker'),
            \DI\get('Metrics')
        ),
    'Json'        => \DI\create(\Transformer\Encoder\Json::class),
    'RulesLoader' => \DI\create(\Service\RulesLoader::class)
        ->constructor(\DI\get('Json')),
    'Metrics' => \DI\create(\Infrastructure\Metrics\Metrics::class)
];
