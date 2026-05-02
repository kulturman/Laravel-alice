<?php

declare(strict_types=1);

namespace Kulturman\LaravelAlice\Tests;

use Kulturman\LaravelAlice\Testing\WithFixtures;

final class WithFixturesTraitTest extends TestCase
{
    use WithFixtures;

    public function test_fixtures_are_loaded_automatically(): void
    {
        $this->assertNotEmpty($this->fixtures);
        $this->assertDatabaseCount('companies', 4);
    }

    public function test_test_specific_fixture_is_loaded(): void
    {
        // 2 from global users.yaml + 1 from WithFixturesTraitTest.yaml
        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseHas('users', ['name' => 'Extra User', 'email' => 'extra@example.com']);
    }
}
