<?php

declare(strict_types=1);

namespace Kulturman\LaravelAlice\Purger;

use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Fidry\AliceDataFixtures\Persistence\PurgerFactoryInterface;
use Fidry\AliceDataFixtures\Persistence\PurgerInterface;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Schema;

final class EloquentPurger implements PurgerInterface, PurgerFactoryInterface
{
    private PurgeMode $purgeMode;

    /** @param list<string> $excludedTables */
    public function __construct(
        private readonly DatabaseManager $databaseManager,
        private readonly array $excludedTables = [],
        ?PurgeMode $purgeMode = null,
    ) {
        $this->purgeMode = $purgeMode ?? PurgeMode::createDeleteMode();
    }

    public function create(PurgeMode $mode, ?PurgerInterface $purger = null): PurgerInterface
    {
        return new self($this->databaseManager, $this->excludedTables, $mode);
    }

    public function purge(): void
    {
        if ($this->purgeMode == PurgeMode::createNoPurgeMode()) {
            return;
        }

        $connection = $this->databaseManager->connection();
        $tables = $this->getTables($connection);

        $connection->getSchemaBuilder()->disableForeignKeyConstraints();

        try {
            foreach ($tables as $table) {
                if (in_array($table, $this->excludedTables, true)) {
                    continue;
                }

                if ($this->purgeMode == PurgeMode::createTruncateMode()) {
                    $connection->table($table)->truncate();
                } else {
                    $connection->table($table)->delete();
                }
            }
        } finally {
            $connection->getSchemaBuilder()->enableForeignKeyConstraints();
        }
    }

    /** @return list<string> */
    private function getTables(ConnectionInterface $connection): array
    {
        return $connection->getSchemaBuilder()->getTableListing();
    }
}
