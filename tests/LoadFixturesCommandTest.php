<?php

declare(strict_types=1);

namespace Kulturman\LaravelAlice\Tests;

final class LoadFixturesCommandTest extends TestCase
{
    public function test_command_loads_fixtures(): void
    {
        $this->artisan('alice:fixtures:load', ['--no-interaction' => true])
            ->assertSuccessful();

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('companies', 4);
    }

    public function test_command_with_append_flag(): void
    {
        $this->artisan('alice:fixtures:load', ['--no-interaction' => true])
            ->assertSuccessful();

        $this->artisan('alice:fixtures:load', ['--append' => true, '--no-interaction' => true])
            ->assertSuccessful();

        $this->assertDatabaseCount('users', 4);
        $this->assertDatabaseCount('companies', 8);
    }

    public function test_command_with_custom_path(): void
    {
        $this->artisan('alice:fixtures:load', [
            '--path' => __DIR__ . '/Fixtures',
            '--no-interaction' => true,
        ])->assertSuccessful();

        $this->assertDatabaseCount('users', 2);
    }

    public function test_command_warns_when_no_fixtures_found(): void
    {
        $this->artisan('alice:fixtures:load', [
            '--path' => '/nonexistent/path',
            '--no-interaction' => true,
        ])->assertSuccessful()
            ->expectsOutput('No fixture files found in: /nonexistent/path');
    }
}
