<?php

declare(strict_types=1);

namespace Kulturman\LaravelAlice\Testing;

use Kulturman\LaravelAlice\Loader\EloquentFixtureLoader;

trait WithFixtures
{
    use UseFixtures;

    /** @var array<string, object> */
    protected array $fixtures = [];

    protected function setUpWithFixtures(): void
    {
        $files = $this->discoverGlobalFixtures();
        $testFixture = $this->discoverTestFixture();

        if ($testFixture !== null) {
            $files[] = $testFixture;
        }

        $this->fixtures = $this->loadFixtures($files);
    }

    /** @return list<string> */
    private function discoverGlobalFixtures(): array
    {
        /** @var EloquentFixtureLoader $loader */
        $loader = $this->app->make(EloquentFixtureLoader::class);

        return $loader->discoverFixtures(config('alice.fixtures_path'));
    }

    private function discoverTestFixture(): ?string
    {
        $reflector = new \ReflectionClass(static::class);
        $testDir = dirname($reflector->getFileName());

        $baseName = $reflector->getShortName();

        foreach (['yaml', 'yml'] as $ext) {
            $path = $testDir . '/' . $baseName . '.' . $ext;
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }
}
