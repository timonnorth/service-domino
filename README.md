# domino

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status](https://travis-ci.org/timonnorth/service-domino.svg?branch=master)](https://travis-ci.org/timonnorth/service-domino)
[![Coverage Status](https://scrutinizer-ci.com/g/timonnorth/service-domino/badges/coverage.png?b=master)][link-scrutinizer]
[![Quality Score](https://scrutinizer-ci.com/g/timonnorth/service-domino/badges/quality-score.png?b=master)][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Forked from: https://github.com/thephpleague/skeleton

**Note:** Replace ```Oleksandr Ieremeev``` ```oitimon``` ```https://github.com/oitimon``` ```oitimon@example.com``` ```oitimon``` ```domino``` ```Domino API server``` with their correct values in [README.md](README.md), [CHANGELOG.md](CHANGELOG.md), [CONTRIBUTING.md](CONTRIBUTING.md), [LICENSE.md](LICENSE.md) and [composer.json](composer.json) files, then delete this line. You can run `$ php prefill.php` in the command line to make all replacements at once. Delete the file prefill.php as well.

This is where your description should go. Try and limit it to a paragraph or two, and maybe throw in a mention of what
PSRs you support to avoid any confusion with users and contributors.

## Structure

If any of the following are applicable to your project, then the directory structure should follow industry best practices by being named the following.

```
bin/        
build/
docs/
config/
src/
tests/
vendor/
```


## Install

Via Composer

``` bash
$ composer require oitimon/domino
```

## Usage

``` php
$skeleton = new Oitimon\Domino();
echo $skeleton->echoPhrase('Hello, League!');
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email oitimon@example.com instead of using the issue tracker.

## Credits

- [Oleksandr Ieremeev][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/oitimon/domino.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/oitimon/domino/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/oitimon/domino.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/oitimon/domino.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/oitimon/domino.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/oitimon/domino
[link-travis]: https://travis-ci.org/oitimon/domino
[link-scrutinizer]: https://scrutinizer-ci.com/g/oitimon/domino/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/oitimon/domino
[link-downloads]: https://packagist.org/packages/oitimon/domino
[link-author]: https://github.com/oitimon
[link-contributors]: ../../contributors
