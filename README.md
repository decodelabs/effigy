# Effigy

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/effigy?style=flat)](https://packagist.org/packages/decodelabs/effigy)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/effigy.svg?style=flat)](https://packagist.org/packages/decodelabs/effigy)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/effigy.svg?style=flat)](https://packagist.org/packages/decodelabs/effigy)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/decodelabs/effigy/integrate.yml?branch=develop)](https://github.com/decodelabs/effigy/actions/workflows/integrate.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/effigy?style=flat)](https://packagist.org/packages/decodelabs/effigy)

### Universal CLI entry point

Effigy is a globally installed universal CLI tool for easily running tasks in your application.

---


## Installation

```bash
composer global require decodelabs/effigy
```

You will also need to have added your global composer installation bin directory to your $PATH in your ~/.bash_profile or ~/.bashrc file:

```bash
export PATH=~/.config/composer/vendor/bin:$PATH
```

_Note, earlier versions of composer may store global config in `~/.composer/vendor/bin` - adapt your $PATH as necessary. You can find composer's home path with `composer global config home`_

## Usage

Effigy can be used to simplify running tasks in your project from the command line. Its primary job is to locate and load the main entry point to your project via a globally installed executable.

Say for example, you currently run commands in your project though `webroot/index.php` as your primary entry point:

```bash
php webroot/index.php run-task
```

Define your entry point in your composer.json file:

```json
{
    "extra": {
        "effigy": {
            "entry": "webroot/index.php"
        }
    }
}
```

Then you can run CLI commands available in your project via the `effigy` executable directly:

```bash
effigy run-task
```

Should you need per-environment entry files, specify template keys in your composer config:

```json
{
    "extra": {
        "effigy": {
            "entry": "entry/{{env}}.php"
        }
    }
}
```

Then on first run, Effigy will ask for the "env" parameter and save it in a local config file (which gets added to your .gitignore).


### Local installation

If you don't want to install Effigy globally, you can use it as a local executable in your project.

```bash
composer require decodelabs/effigy
vendor/bin/effigy install-local
```

You can then call effigy like so:

```bash
./effigy run-task
```

### PHP binary

Effigy can use alternative versions of PHP on a per-project basis:

```bash
effigy set-php
> php8.1
```

The bin path is stored in your local config and all process launches will use this going forward. Reset it to "php" to use the default system global binary.


### Composer passthrough

Effigy will attempt to run scripts defined in your composer.json:

```json
{
    "scripts": {
        "analyze": "phpstan analyze"
    }
}
```

```bash
effigy analyze
```

You can also run composer commands through effigy directly:

```bash
effigy composer require decodelabs/atlas
```

This is especially useful if you have defined an alternative version of PHP for your project as global composer will use global PHP.

## Licensing
Effigy is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.
