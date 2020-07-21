<?php

declare(strict_types=1);

namespace Service;

use Transformer\Encoder\EncoderInterface;
use ValueObject\Rules;

class RulesLoader
{
    /** @var EncoderInterface */
    protected $encoder;

    public function __construct(EncoderInterface $jsonEncoder)
    {
        $this->encoder = $jsonEncoder;
    }

    /**
     * Load rules from resources by its name.
     *
     * @throws \Transformer\Encoder\Exception
     */
    public function loadRules(string $rulesName): ?Rules
    {
        $filename = sprintf('%s/resources/rules/%s.json', __APPDIR__, $rulesName);

        if (is_file($filename)) {
            $rules       = Rules::createByParameters($this->encoder->decode(file_get_contents($filename)));
            $rules->name = $rulesName;
        } else {
            $rules = null;
        }

        return $rules;
    }

    /**
     * Scans resource folder and returns all rule's names.
     */
    public function getAllRulesNames(): array
    {
        $names = [];
        $files = scandir(sprintf('%s/resources/rules', __APPDIR__));

        if (is_array($files)) {
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) == 'json') {
                    $names[] = pathinfo($file, PATHINFO_FILENAME);
                }
            }
        }

        return $names;
    }
}
