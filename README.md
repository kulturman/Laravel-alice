# Laravel Alice

YAML-based fixture loading for Laravel, powered by [nelmio/alice](https://github.com/nelmio/alice) and [theofidry/alice-data-fixtures](https://github.com/theofidry/AliceDataFixtures).

## Requirements

- PHP 8.2+
- Laravel 11+

## Installation

```bash
composer require kulturman/laravel-alice
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=alice-config
```

## Configuration

```php
// config/alice.php

return [
    'fixtures_path' => database_path('fixtures'),
    'purge_mode' => 'delete', // delete | truncate | none
    'excluded_tables' => ['migrations', 'jobs', 'failed_jobs'],
    'providers' => [
        // Custom Faker providers:
        // App\Fixtures\Providers\MyProvider::class,
    ],
];
```

## Writing Fixtures

Create YAML files in `database/fixtures/`:

```yaml
# database/fixtures/companies.yaml
App\Models\Company:
    company_acme:
        name: "Acme Corp"
    company_{1..10}:
        name: "<company()>"
```

```yaml
# database/fixtures/users.yaml
App\Models\User:
    user_admin:
        name: "Admin"
        email: "admin@example.com"
        company_id: 1
    user_{1..50}:
        name: "<firstName()> <lastName()>"
        email: "<email()>"
        company_id: "<numberBetween(1, 11)>"
```

For the full YAML reference, see the [nelmio/alice documentation](https://github.com/nelmio/alice/blob/master/doc/complete-reference.md).

## Usage

### Artisan Command

```bash
# Load all fixtures (with purge confirmation)
php artisan alice:fixtures:load

# Append without purging
php artisan alice:fixtures:load --append

# Use truncate instead of delete
php artisan alice:fixtures:load --purge=truncate

# Load from a custom path
php artisan alice:fixtures:load --path=database/fixtures/dev

# Skip confirmation
php artisan alice:fixtures:load --no-interaction
```

### In Tests

Use the `UseFixtures` trait to load fixtures in your test cases:

```php
use Kulturman\LaravelAlice\Testing\UseFixtures;

class OrderTest extends TestCase
{
    use RefreshDatabase, UseFixtures;

    public function test_order_total(): void
    {
        // Load all fixtures from the configured path
        $objects = $this->loadFixtures();

        // Or load specific files
        $objects = $this->loadFixtures([
            database_path('fixtures/companies.yaml'),
            database_path('fixtures/users.yaml'),
        ]);

        // $objects is an array of persisted models indexed by fixture name
        $this->assertNotEmpty($objects);
    }
}
```

### Custom Faker Providers

Create a provider class and register it in the config:

```php
namespace App\Fixtures\Providers;

use Faker\Provider\Base;

class ProductProvider extends Base
{
    public function sku(): string
    {
        return 'SKU-' . $this->generator->unique()->numberBetween(10000, 99999);
    }
}
```

```php
// config/alice.php
'providers' => [
    App\Fixtures\Providers\ProductProvider::class,
],
```

Then use it in fixtures:

```yaml
App\Models\Product:
    product_{1..100}:
        sku: "<sku()>"
        name: "<productName()>"
```

## License

MIT
