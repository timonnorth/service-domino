<?php

declare(strict_types=1);

namespace Infrastructure\Persistence;

use Infrastructure\Persistence\File\FileMatchRepository;
use Infrastructure\Persistence\Redis\RedisMatchRepository;
use Service\Repository\Exception;
use Service\Repository\MatchRepositoryInterface;
use Transformer\Serializer;

class MatchRepositoryManager
{
    protected const FILESYSTEM = 'filesystem';
    protected const REDIS      = 'redis';

    /** @var Serializer */
    protected $serializer;
    /** @var MatchRepositoryInterface */
    protected $repository;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @throws Exception
     */
    public function getRepository(): MatchRepositoryInterface
    {
        if ($this->repository === null) {
            $storage = getenv('APP_MATCH_STORAGE');

            if (!$storage) {
                throw new Exception('Env variable APP_MATCH_STORAGE is not defined');
            }
            $storage = strtolower($storage);

            switch ($storage) {
                case self::FILESYSTEM:
                    $dir = getenv('APP_MATCH_STORAGE_DIR');

                    if ($dir == '') {
                        $dir = sprintf('%s/var/tmp/filestorage', __APPDIR__);
                    }
                    $repository = new FileMatchRepository($this->serializer, $dir);

                    break;

                case self::REDIS:
                    $repository = new RedisMatchRepository(
                        $this->serializer,
                        new \Predis\Client(getenv('APP_REDIS_PARAMS')),
                        (string)getenv('APP_MATCH_STORAGE_PREFIX')
                    );

                    break;

                default:
                    throw new Exception(sprintf('Undefined storage: %s', $storage));

                    break;
            }
            $this->setRepository($repository);
        }

        return $this->repository;
    }

    public function setRepository(MatchRepositoryInterface $repository): void
    {
        $this->repository = $repository;
    }
}
