<?php

declare(strict_types=1);

namespace Kulturman\LaravelAlice\Console;

use Illuminate\Console\Command;
use Kulturman\LaravelAlice\Loader\EloquentFixtureLoader;

final class LoadFixturesCommand extends Command
{
    protected $signature = 'alice:fixtures:load
        {--purge=delete : Purge mode: delete, truncate, or none}
        {--path= : Path to fixture files (defaults to config value)}
        {--append : Append fixtures without purging existing data}';

    protected $description = 'Load YAML fixtures into the database';

    public function handle(EloquentFixtureLoader $loader): int
    {
        $path = $this->option('path') ?? config('alice.fixtures_path');
        $purgeMode = $this->option('append') ? 'none' : $this->option('purge');

        $files = $loader->discoverFixtures($path);

        if ($files === []) {
            $this->warn("No fixture files found in: {$path}");

            return self::SUCCESS;
        }

        $this->info(sprintf('Found %d fixture file(s) in %s', count($files), $path));

        foreach ($files as $file) {
            $this->line("  - {$file}");
        }

        if ($purgeMode !== 'none' && !$this->option('no-interaction')) {
            if (!$this->confirm('This will purge existing data. Continue?')) {
                $this->info('Aborted.');

                return self::SUCCESS;
            }
        }

        $resolvedPurgeMode = EloquentFixtureLoader::resolvePurgeMode($purgeMode);
        $objects = $loader->load($files, [], $resolvedPurgeMode);

        $this->info(sprintf('Loaded %d fixture(s) successfully.', count($objects)));

        return self::SUCCESS;
    }
}
