<?php

declare(strict_types=1);

namespace Infrastructure\Persistence\File;

use Entity\Match;
use Infrastructure\Persistence\MatchRepositoryAbstract;
use Transformer\Serializer;

class FileMatchRepository extends MatchRepositoryAbstract
{
    /** @var string */
    protected $folder;

    public function __construct(Serializer $serializer, string $folder)
    {
        $this->serializer = $serializer;
        $this->folder     = $folder;
    }

    public function load(string $id): ?Match
    {
        $match    = null;
        $filename = $this->formatFilename($id);

        if ($id != '' && is_file($filename)) {
            $match = $this->deserialize(file_get_contents($filename));
        }

        return $match;
    }

    /**
     * @throws \Transformer\Encoder\Exception
     */
    public function save(Match $match): void
    {
        if ($match->id != '') {
            file_put_contents($this->formatFilename($match->id), $this->serializer->serialize($match));
        }
    }

    protected function formatFilename(string $id): string
    {
        return sprintf('%s/match-%s', $this->folder, $id);
    }
}
