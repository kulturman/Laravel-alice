<?php

return [
    'fixtures_path' => database_path('fixtures'),

    // delete | truncate | none
    'purge_mode' => 'delete',

    'excluded_tables' => ['migrations', 'jobs', 'failed_jobs'],

    'providers' => [
        // Custom Faker providers:
        // App\Fixtures\Providers\MyProvider::class,
    ],
];
