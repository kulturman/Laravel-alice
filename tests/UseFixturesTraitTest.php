<?php

declare(strict_types=1);

namespace Kulturman\LaravelAlice\Tests;

use Kulturman\LaravelAlice\Testing\UseFixtures;

final class UseFixturesTraitTest extends TestCase
{
    use UseFixtures;

    public function test_trait_loads_all_fixtures_from_config_path(): void
    {
        $objects = $this->loadFixtures();

        $this->assertNotEmpty($objects);
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('companies', 4);
    }

    public function test_trait_loads_specific_files(): void
    {
        $objects = $this->loadFixtures([__DIR__ . '/Fixtures/users.yaml']);

        $this->assertNotEmpty($objects);
        $this->assertDatabaseCount('users', 2);
    }
}
