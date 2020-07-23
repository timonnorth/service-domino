<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\Redis;

use Entity\Match;
use Infrastructure\Persistence\MatchRepositoryAbstract;
use Predis\Client;
use Service\Repository\Exception;
use Transformer\Serializer;

class RedisMatchRepository extends MatchRepositoryAbstract
{
    /**
     * We do not want to store matches forever, so let it be ne week.
     *
     * @todo Can be configurable later.
     */
    protected const TTL = 604800;
    /** Delay in milliseconds */
    protected const SAVE_DELAY = 300;

    /** @var Client */
    protected $client;
    /** @var string */
    protected $keyPrefix;

    public function __construct(Serializer $serializer, Client $client, $keyPrefix = '')
    {
        $this->serializer = $serializer;
        $this->client     = $client;
        $this->keyPrefix  = $keyPrefix == '' ? '' : $keyPrefix . '_';
    }

    public function load(string $id): ?Match
    {
        return $id == '' ? null : $this->deserialize($this->client->get($this->formatKey($id)));
    }

    /**
     * @throws \Transformer\Encoder\Exception
     * @throws Exception
     */
    public function save(Match $match): void
    {
        if ($match->id != '') {
            for ($i = 1; $i <= 5; $i++) {
                $res = $this->client->setex(
                    $this->formatKey($match->id),
                    self::TTL,
                    $this->serializer->serialize($match)
                );

                if ($res) {
                    break;
                }
                usleep(self::SAVE_DELAY * 1000);
            }

            if (!$res) {
                throw new Exception("Can not save Match in Redis");
            }
        }
    }

    protected function formatKey(string $id): string
    {
        return $this->keyPrefix . 'match-' . $id;
    }
}
