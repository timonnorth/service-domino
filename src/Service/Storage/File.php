<?php

declare(strict_types=1);

namespace Service\Storage;

use Entity\Match;
use Transformer\Serializer;

class File implements StorageInterface
{
    /** @var Serializer */
    protected $serializer;
    /** @var string */
    protected $folder;

    public function __construct(Serializer $serializer, string $folder)
    {
        $this->serializer = $serializer;
        $this->folder     = $folder;
    }

    public function getMatch(string $id): ?Match
    {
        $match    = null;
        $filename = $this->formatMatchFilename($id);

        if ($id != '' && is_file($filename)) {
            $match = $this->deserializeMatch(file_get_contents($filename));
        }

        return $match;
    }

    public function setMatch(Match $match): void
    {
        if ($match->id != '') {
            file_put_contents($this->formatMatchFilename($match->id), $this->serializer->serialize($match));
        }
    }

    protected function deserializeMatch(string $data): ?Match
    {
        $match = null;

        if ($data !== false && $data != '') {
            $match = $this->serializer->deserialize($data);

            if (!($match instanceof Match)) {
                // Do not generate error, just ignore not correct value.
                $match = null;
            }
        }

        return $match;
    }

    protected function formatMatchFilename(string $id): string
    {
        return sprintf('%s/match-%s', $this->folder, $id);
    }
}
