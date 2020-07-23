<?php

declare(strict_types=1);

namespace Infrastructure\Persistence;

use Entity\Match;
use Service\Repository\MatchRepositoryInterface;
use Transformer\Serializer;

abstract class MatchRepositoryAbstract implements MatchRepositoryInterface
{
    /** @var Serializer */
    protected $serializer;

    protected function deserialize($data): ?Match
    {
        $match = null;

        if ($data != '') {
            $match = $this->serializer->deserialize((string)$data);

            if (!($match instanceof Match)) {
                // Do not generate error, just ignore not correct value.
                $match = null;
            }
        }

        return $match;
    }
}
