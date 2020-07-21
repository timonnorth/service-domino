<?php

return [
    'Serializer' => \DI\create(\Transformer\Serializer::class)
        ->constructor(new \Transformer\Encoder\Json()),
    'Storage' => \DI\create(\Service\Storage\File::class)
        ->constructor(
            \DI\get('Serializer'),
            sprintf('%s/var/tmp/filestorage', __APPDIR__)
        ),
    'GameFactory' => \DI\create(\Service\GameFactory::class)
        ->constructor(
            \DI\get('RulesLoader'),
            \DI\get('Storage'),
            \DI\get('Locker')
        ),
    'Json'        => \DI\create(\Transformer\Encoder\Json::class),
    'RulesLoader' => \DI\create(\Service\RulesLoader::class)
        ->constructor(\DI\get('Json')),
];
