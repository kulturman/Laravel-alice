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
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('companies', 4);
    }
}
