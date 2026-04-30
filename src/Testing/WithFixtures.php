<?php

declare(strict_types=1);

namespace Kulturman\LaravelAlice\Testing;

trait WithFixtures
{
    use UseFixtures;

    /** @var array<string, object> */
    protected array $fixtures = [];

    protected function setUpWithFixtures(): void
    {
        $this->fixtures = $this->loadFixtures();
    }
}
