<?php

declare(strict_types=1);

namespace Kulturman\LaravelAlice\Tests;

use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Kulturman\LaravelAlice\Loader\EloquentFixtureLoader;
use Kulturman\LaravelAlice\Tests\Models\Company;
use Kulturman\LaravelAlice\Tests\Models\User;

final class EloquentFixtureLoaderTest extends TestCase
{
    public function test_loads_fixtures_and_persists_to_database(): void
    {
        /** @var EloquentFixtureLoader $loader */
        $loader = $this->app->make(EloquentFixtureLoader::class);

        $objects = $loader->load(
            [__DIR__ . '/Fixtures/users.yaml'],
            [],
            PurgeMode::createNoPurgeMode(),
        );

        $this->assertNotEmpty($objects);
        $this->assertDatabaseCount('companies', 1);
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseHas('users', ['name' => 'John Doe', 'email' => 'john@example.com']);
        $this->assertDatabaseHas('companies', ['name' => 'Acme Corp']);
    }

    public function test_resolves_references_between_models(): void
    {
        /** @var EloquentFixtureLoader $loader */
        $loader = $this->app->make(EloquentFixtureLoader::class);

        $loader->load(
            [__DIR__ . '/Fixtures/users.yaml'],
            [],
            PurgeMode::createNoPurgeMode(),
        );

        $company = Company::first();
        $user = User::where('name', 'John Doe')->first();

        $this->assertEquals($company->id, $user->company_id);
    }

    public function test_loads_fixtures_with_faker_data(): void
    {
        /** @var EloquentFixtureLoader $loader */
        $loader = $this->app->make(EloquentFixtureLoader::class);

        $loader->load(
            [__DIR__ . '/Fixtures/companies.yaml'],
            [],
            PurgeMode::createNoPurgeMode(),
        );

        $this->assertDatabaseCount('companies', 3);
        $companies = Company::all();
        foreach ($companies as $company) {
            $this->assertNotEmpty($company->name);
        }
    }

    public function test_discovers_yaml_files_in_directory(): void
    {
        /** @var EloquentFixtureLoader $loader */
        $loader = $this->app->make(EloquentFixtureLoader::class);

        $files = $loader->discoverFixtures(__DIR__ . '/Fixtures');

        $this->assertNotEmpty($files);
        $this->assertCount(2, $files);
    }

    public function test_returns_empty_array_for_nonexistent_path(): void
    {
        /** @var EloquentFixtureLoader $loader */
        $loader = $this->app->make(EloquentFixtureLoader::class);

        $files = $loader->discoverFixtures('/nonexistent/path');

        $this->assertSame([], $files);
    }

    public function test_purge_delete_mode_clears_data(): void
    {
        /** @var EloquentFixtureLoader $loader */
        $loader = $this->app->make(EloquentFixtureLoader::class);

        // Load once
        $loader->load(
            [__DIR__ . '/Fixtures/users.yaml'],
            [],
            PurgeMode::createNoPurgeMode(),
        );

        $this->assertDatabaseCount('users', 2);

        // Load again with delete purge — should reset
        $loader->load(
            [__DIR__ . '/Fixtures/users.yaml'],
            [],
            PurgeMode::createDeleteMode(),
        );

        $this->assertDatabaseCount('users', 2);
    }

    public function test_no_purge_mode_appends_data(): void
    {
        /** @var EloquentFixtureLoader $loader */
        $loader = $this->app->make(EloquentFixtureLoader::class);

        $loader->load(
            [__DIR__ . '/Fixtures/users.yaml'],
            [],
            PurgeMode::createNoPurgeMode(),
        );

        $loader->load(
            [__DIR__ . '/Fixtures/users.yaml'],
            [],
            PurgeMode::createNoPurgeMode(),
        );

        // Two loads without purge = duplicated data
        $this->assertDatabaseCount('companies', 2);
        $this->assertDatabaseCount('users', 4);
    }
}
