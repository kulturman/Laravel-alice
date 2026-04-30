<?php

declare(strict_types=1);

namespace Kulturman\LaravelAlice;

use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;
use Kulturman\LaravelAlice\Console\LoadFixturesCommand;
use Kulturman\LaravelAlice\Loader\EloquentFixtureLoader;

final class LaravelAliceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/alice.php', 'alice');

        $this->app->singleton(EloquentFixtureLoader::class, function ($app) {
            /** @var array{providers: list<string>, excluded_tables: list<string>, purge_mode: string} $config */
            $config = $app['config']['alice'];

            return new EloquentFixtureLoader(
                databaseManager: $app->make(DatabaseManager::class),
                providers: $config['providers'],
                excludedTables: $config['excluded_tables'],
                defaultPurgeMode: $config['purge_mode'],
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/alice.php' => config_path('alice.php'),
            ], 'alice-config');

            $this->commands([LoadFixturesCommand::class]);
        }
    }
}
