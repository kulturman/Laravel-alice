# Laravel Alice Package — Implementation Context

## Goal

Build a Laravel package that wraps `nelmio/alice` + `theofidry/alice-data-fixtures` to provide YAML-based fixture loading with Eloquent persistence. Same DX as `hautelook/alice-bundle` for Symfony.

## Dependencies

### nelmio/alice (core YAML fixture engine)

- **Packagist:** https://packagist.org/packages/nelmio/alice
- **GitHub:** https://github.com/nelmio/alice
- **Version:** ^3.17
- **Role:** Parses YAML fixture files, resolves references, generates PHP objects via Faker

**Standalone usage (no framework):**
```php
use Nelmio\Alice\Loader\NativeLoader;

$loader = new NativeLoader();
$objectSet = $loader->loadFile(__DIR__.'/fixtures/User.yml');
// $objectSet->getObjects() returns hydrated PHP objects (not persisted)
```

**YAML format features:**
- Templates: `user (template):` — not persisted, used as base
- Inheritance: `user_admin (extends user):` — inherits template fields
- Ranges: `user_{1..50}:` — generates 50 entities, `<current()>` gives index
- References: `company: "@company_keyops"` — links to other fixture by name
- Faker: `email: <email()>`, `name: <firstName()>`
- PHP expressions: `ulid: <(MyClass::fromString("..."))>`
- Optionals: `phone: 50%? <phoneNumber()>`
- Custom providers: register classes implementing Faker provider interface

**Key classes:**
- `Nelmio\Alice\Loader\NativeLoader` — entry point, configurable
- `Nelmio\Alice\ObjectSet` — result of loading (iterable objects)
- `Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\FakerFunctionCallParser`
- Override `NativeLoader::createFakerGenerator()` to add custom providers

### theofidry/alice-data-fixtures (persistence layer)

- **Packagist:** https://packagist.org/packages/theofidry/alice-data-fixtures
- **GitHub:** https://github.com/theofidry/AliceDataFixtures
- **Version:** ^1.11
- **Role:** Takes Alice-generated objects and persists them via Eloquent (or Doctrine, etc.)

**Eloquent support:**
- Ships an Eloquent bridge out of the box
- Namespace: `Fidry\AliceDataFixtures\Bridge\Eloquent`
- Loader: `Fidry\AliceDataFixtures\Bridge\Eloquent\EloquentLoader` (implements `LoaderInterface`)
- Purger: `Fidry\AliceDataFixtures\Bridge\Eloquent\Purger\ModelPurger`

**LoaderInterface:**
```php
namespace Fidry\AliceDataFixtures;

interface LoaderInterface
{
    /** @return object[] indexed by fixture name */
    public function load(array $fixturesFiles, array $parameters = [], array $objects = [], PurgeMode $purgeMode = null): array;
}
```

**Purge modes:**
- `PurgeMode::createDeleteMode()` — DELETE FROM each table
- `PurgeMode::createTruncateMode()` — TRUNCATE each table
- `PurgeMode::createNoPurgeMode()` — don't purge, append data

**Table exclusion:** configurable list of tables to skip during purge (e.g., `migrations`).

### hautelook/alice-bundle (reference implementation for Symfony)

- **GitHub:** https://github.com/hautelook/AliceBundle
- **Role:** Reference for how to wrap Alice for a framework. Study its service wiring, fixture discovery, and command structure.

## Package Structure

```
laravel-alice/
├── composer.json
├── config/
│   └── alice.php                  # publishable config
├── src/
│   ├── LaravelAliceServiceProvider.php
│   ├── Console/
│   │   └── LoadFixturesCommand.php   # php artisan alice:fixtures:load
│   ├── Loader/
│   │   └── EloquentFixtureLoader.php # wraps alice + persistence
│   └── Testing/
│       └── UseFixtures.php           # trait for PHPUnit/Pest
├── tests/
└── README.md
```

## Config File (config/alice.php)

```php
return [
    'fixtures_path' => database_path('fixtures'),
    'purge_mode' => 'delete', // delete | truncate | none
    'excluded_tables' => ['migrations', 'jobs', 'failed_jobs'],
    'providers' => [
        // Custom Faker providers
        // App\Fixtures\Providers\MyProvider::class,
    ],
];
```

## Key Implementation Points

### Object Hydration with Eloquent

Alice instantiates objects via property access / setters. Eloquent models use `__set` magic which writes to `$attributes`. This generally works BUT:

- `$fillable` / `$guarded` do NOT apply to `$model->attribute = $value` (only to mass assignment like `fill()` / `create()`). So direct property setting bypasses guarded — this is fine for fixtures.
- Casts are applied on `setAttribute()`, so casting works normally.
- Relationships: Alice sets the FK value directly (e.g., `company_id: 1`) or the loader must resolve `@references` to IDs after persistence.

### Relationship Handling Strategy

Two approaches:
1. **Simple (FK-based):** Fixtures declare `company_id: "@company->id"` — Alice resolves the reference, gets the ID after the referenced object is persisted first.
2. **Elegant (Alice-native):** Fixtures declare `company: "@company_keyops"` — the persistence layer calls `$model->company()->associate($referencedModel)` before save.

The `theofidry/alice-data-fixtures` Eloquent bridge handles approach #1 natively (persist in dependency order, resolve references to persisted objects).

### Fixture Loading Order

Alice resolves dependencies automatically from `@references`. Objects are persisted in topological order (dependencies first). Cross-file references work as long as all files are loaded together.

### Artisan Command

```
php artisan alice:fixtures:load [--purge=delete] [--path=database/fixtures] [--append]
```

- Discovers all `*.yml` / `*.yaml` files in fixtures path
- Confirms before purging (interactive)
- `--append` sets purge mode to none

### Test Trait

```php
trait UseFixtures
{
    protected function loadFixtures(array $files = []): array
    {
        // If no files specified, load all from config path
        // Returns array of persisted objects indexed by fixture name
    }
}
```

## Links

| Resource | URL |
|----------|-----|
| nelmio/alice GitHub | https://github.com/nelmio/alice |
| nelmio/alice docs | https://github.com/nelmio/alice/blob/master/doc/getting-started.md |
| alice-data-fixtures GitHub | https://github.com/theofidry/AliceDataFixtures |
| alice-data-fixtures Eloquent | https://github.com/theofidry/AliceDataFixtures/blob/master/doc/eloquent.md |
| hautelook/AliceBundle (reference) | https://github.com/hautelook/AliceBundle |
| Alice YAML reference | https://github.com/nelmio/alice/blob/master/doc/complete-reference.md |
| Alice custom providers | https://github.com/nelmio/alice/blob/master/doc/customizing-data-generation.md |

