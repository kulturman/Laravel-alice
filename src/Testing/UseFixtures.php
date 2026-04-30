<?php

declare(strict_types=1);

namespace Kulturman\LaravelAlice\Testing;

use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Kulturman\LaravelAlice\Loader\EloquentFixtureLoader;

trait UseFixtures
{
    /**
     * @param list<string> $files Fixture files to load. If empty, loads all from config path.
     * @param array<string, mixed> $parameters
     * @return array<string, object>
     */
    protected function loadFixtures(array $files = [], array $parameters = [], ?PurgeMode $purgeMode = null): array
    {
        /** @var EloquentFixtureLoader $loader */
        $loader = $this->app->make(EloquentFixtureLoader::class);

        if ($files === []) {
            $path = config('alice.fixtures_path');
            $files = $loader->discoverFixtures($path);
        }

        return $loader->load($files, $parameters, $purgeMode ?? PurgeMode::createNoPurgeMode());
    }
}
