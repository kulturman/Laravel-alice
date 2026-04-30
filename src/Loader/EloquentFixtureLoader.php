<?php

declare(strict_types=1);

namespace Kulturman\LaravelAlice\Loader;

use Faker\Generator as FakerGenerator;
use Fidry\AliceDataFixtures\Bridge\Eloquent\Persister\ModelPersister;
use Fidry\AliceDataFixtures\Loader\PersisterLoader;
use Fidry\AliceDataFixtures\Loader\PurgerLoader;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Illuminate\Database\DatabaseManager;
use Kulturman\LaravelAlice\Purger\EloquentPurger;
use Nelmio\Alice\Loader\NativeLoader;
use Symfony\Component\Finder\Finder;

final class EloquentFixtureLoader
{
    private readonly NativeLoader $nativeLoader;

    /**
     * @param list<string> $providers
     * @param list<string> $excludedTables
     */
    public function __construct(
        private readonly DatabaseManager $databaseManager,
        private readonly array $providers = [],
        private readonly array $excludedTables = [],
        private readonly string $defaultPurgeMode = 'delete',
    ) {
        $this->nativeLoader = $this->createNativeLoader();
    }

    /**
     * @param list<string> $files
     * @param array<string, mixed> $parameters
     * @return array<string, object>
     */
    public function load(array $files, array $parameters = [], ?PurgeMode $purgeMode = null): array
    {
        $purgeMode ??= $this->resolvePurgeMode($this->defaultPurgeMode);

        $persister = new ModelPersister($this->databaseManager);
        $adapter = new NativeLoaderAdapter($this->nativeLoader);
        $persisterLoader = new PersisterLoader(
            $adapter,
            $persister,
        );

        $purger = new EloquentPurger($this->databaseManager, $this->excludedTables);
        $internalPurgeMode = $this->toInternalPurgeMode($this->defaultPurgeMode);
        $purgerLoader = new PurgerLoader($persisterLoader, $purger, $internalPurgeMode);

        return $purgerLoader->load($files, $parameters, [], $purgeMode);
    }

    /** @return list<string> */
    public function discoverFixtures(string $path): array
    {
        if (!is_dir($path)) {
            return [];
        }

        $finder = new Finder();
        $finder->files()->in($path)->name(['*.yml', '*.yaml'])->sortByName();

        $files = [];
        foreach ($finder as $file) {
            $files[] = $file->getRealPath();
        }

        return $files;
    }

    public static function resolvePurgeMode(string $mode): PurgeMode
    {
        return match ($mode) {
            'delete' => PurgeMode::createDeleteMode(),
            'truncate' => PurgeMode::createTruncateMode(),
            'none' => PurgeMode::createNoPurgeMode(),
            default => PurgeMode::createDeleteMode(),
        };
    }

    private static function toInternalPurgeMode(string $mode): string
    {
        return match ($mode) {
            'none' => 'no_purge',
            default => $mode,
        };
    }

    private function createNativeLoader(): NativeLoader
    {
        if ($this->providers === []) {
            return new NativeLoader();
        }

        return new class($this->providers) extends NativeLoader {
            /** @param list<string> $providerClasses */
            public function __construct(private readonly array $providerClasses)
            {
                parent::__construct();
            }

            protected function createFakerGenerator(): FakerGenerator
            {
                $generator = parent::createFakerGenerator();

                foreach ($this->providerClasses as $providerClass) {
                    $generator->addProvider(new $providerClass($generator));
                }

                return $generator;
            }
        };
    }
}
