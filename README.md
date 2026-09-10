# Bitrix IDE Helper

Generate IDE-indexable PHP stubs from the exact 1C-Bitrix installation used by a project.

The package scans both core and local modules, preserves declarations and PHPDoc, and removes executable method bodies. It does not redistribute Bitrix source code and does not bootstrap the application or connect to its database.

## Status

Early MVP. The current release discovers PHP symbols and generates static stubs. Runtime ORM metadata, PhpStorm meta files, legacy type corrections and Bitrix service-locator inference are planned next.

## Installation

```bash
composer require --dev d00p1/bitrix-ide-helper
```

PHP 8.3 or newer is required.

## Discover installed symbols

```bash
vendor/bin/bitrix-ide-helper discover /var/www/site/public
```

The command prints JSON containing modules, their scope, source paths and discovered classes, interfaces, traits and enums.

## Generate stubs

```bash
vendor/bin/bitrix-ide-helper generate /var/www/site/public
```

By default files are written to `.ide-helper/bitrix`. Select modules or change the target directory:

```bash
vendor/bin/bitrix-ide-helper generate /var/www/site/public \
  --module=main \
  --module=iblock \
  --module=catalog \
  --output=.ide-helper/bitrix
```

Do not include generated files at runtime. They are intended only for IDE indexing. If a static analyzer reports duplicate declarations, exclude `.ide-helper` from its analyzed paths.

## Development

```bash
composer install
composer test
```

## Roadmap

- Parse explicit `Loader::registerAutoLoadClasses()` and `Loader::registerNamespace()` registrations.
- Normalize legacy PHPDoc types and enrich missing native types.
- Generate stubs for `DataManager`, `EO_*_Object`, collections, queries and results from ORM metadata.
- Generate `.phpstorm.meta.php` overrides for service locator and factories.
- Add incremental cache and per-module generation reports.

## License

MIT. Generated files are derived locally from the user's installed Bitrix copy and are not part of this package.
