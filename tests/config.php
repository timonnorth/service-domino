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
        ->constructor(\DI\get('Json')),
    'Json' => \DI\create(\Transformer\Encoder\Json::class)
];
