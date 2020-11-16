# Very short description of the package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/jubayed/laravel-generator.svg?style=flat-square)](https://packagist.org/packages/jubayed/laravel-generator)
[![Build Status](https://img.shields.io/travis/jubayed/laravel-generator/master.svg?style=flat-square)](https://travis-ci.org/jubayed/laravel-generator)
[![Quality Score](https://img.shields.io/scrutinizer/g/jubayed/laravel-generator.svg?style=flat-square)](https://scrutinizer-ci.com/g/jubayed/jubayed-generator)
[![Total Downloads](https://img.shields.io/packagist/dt/jubayed/laravel-generator.svg?style=flat-square)](https://packagist.org/packages/jubayed/laravel-generator)

This is where your description should go. Try and limit it to a paragraph or two, and maybe throw in a mention of what PSRs you support to avoid any confusion with users and contributors.

## Installation

You can install the package via composer:

```bash
composer require jubayed/laravel-generator
```

## Usage

``` php
'providers' => [
    // ...
    Jubayed\LaravelGenerator\LaravelGeneratorServiceProvider::class,
];
```

## publish
``` sh
php artisan vendor:publish --provider=Jubayed\LaravelGenerator\LaravelGeneratorServiceProvider
```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email admin@jubayed.com instead of using the issue tracker.

## Credits

- [jubayed](https://github.com/jubayed)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

