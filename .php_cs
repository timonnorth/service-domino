<?php

use Mollie\PhpCodingStandards\PhpCsFixer\Rules;
use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->name('.php_cs') // Fix this file as well
    ->in(__DIR__);

return Config::create()
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    // use specific rules for your php version e.g.: getForPhp71, getForPhp72, getForPhp73
    ->setRules(Rules::getForPhp72());
