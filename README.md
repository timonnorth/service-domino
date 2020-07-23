# DOMINO

[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]

## Install

"Composer require" is not available as project is private. To install it locally, please use git:

``` bash
    git clone https://github.com/timonnorth/service-domino.git
    cd service-domino
    cp docker-compose.override.yml.dist docker-compose.override.yml
    docker-compose up -d
    docker exec -it service-domino bash -c "composer update --optimize-autoloader"
```

## Testing

``` bash
    docker exec -it service-domino bash -c "composer test"
```

## Client

``` bash
    docker exec -it service-domino bash -c "php cli/client.php http://host.docker.internal:8080"
```

If you have PHP installed on your host (>=7.1) you can start client locally:

``` bash
    php cli/client.php http://127.0.0.1:8080
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email oitimon@example.com instead of using the issue tracker.

## Credits

- [Oleksandr Ieremeev][link-author]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/timonnorth/service-domino/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/timonnorth/service-domino.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/timonnorth/service-domino.svg?style=flat-square

[link-travis]: https://travis-ci.org/timonnorth/service-domino?branch=master
[link-scrutinizer]: https://scrutinizer-ci.com/g/timonnorth/service-domino/code-structure?branch=master
[link-code-quality]: https://scrutinizer-ci.com/g/timonnorth/service-domino?branch=master
[link-author]: https://github.com/oitimon
