<?php

return [
    'Serializer' => \DI\create(\Transformer\Serializer::class)
        ->constructor(new \Transformer\Encoder\Json()),
    'Storage' => \DI\create(\Service\Storage\File::class)
        ->constructor(
            \DI\get('Serializer'),
            sprintf('%s/resources/filestorage', __DIR__)
        )
];
