<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\File;

use Service\Repository\Exception;
use Service\Repository\MatchRepositoryInterface;
use Transformer\Serializer;

class MatchRepositoryManager
{
    protected const FILESYSTEM = 'filesystem';

    /** @var Serializer */
    protected $serializer;
    /** @var MatchRepositoryInterface */
    protected $repository;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @return MatchRepositoryInterface
     * @throws Exception
     */
    public function getRepository(): MatchRepositoryInterface
    {
        if ($this->repository === null) {
            $storage = defined('APP_MATCH_STORAGE') ? APP_MATCH_STORAGE : getenv('APP_MATCH_STORAGE');
            if (!$storage) {
                throw new Exception('Env variable APP_MATCH_STORAGE is not defined');
            }
            $storage = strtolower($storage);
            switch ($storage) {
                case self::FILESYSTEM:
                    $dir = defined('APP_MATCH_STORAGE_DIR') ?
                        APP_MATCH_STORAGE_DIR : getenv('APP_MATCH_STORAGE_DIR');
                    if ($dir == '') {
                        $dir = sprintf('%s/var/tmp/filestorage', __APPDIR__);
                    }
                    $repository = new FileMatchRepository($this->serializer, $dir);
                    break;
                default:
                    throw new Exception(sprintf('Undefined storage: %s', $storage));
                    break;
            }
            $this->setReposiroty($repository);
        }
        return $this->repository;
    }

    public function setReposiroty(MatchRepositoryInterface $repository): void
    {
        $this->repository = $repository;
    }
}
