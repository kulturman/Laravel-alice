<?php

declare(strict_types=1);

namespace Kulturman\LaravelAlice\Loader;

use Fidry\AliceDataFixtures\LoaderInterface;
use Fidry\AliceDataFixtures\Persistence\PurgeMode;
use Nelmio\Alice\Loader\NativeLoader;

final class NativeLoaderAdapter implements LoaderInterface
{
    public function __construct(private readonly NativeLoader $nativeLoader)
    {
    }

    public function load(array $fixturesFiles, array $parameters = [], array $objects = [], ?PurgeMode $purgeMode = null): array
    {
        $objectSet = $this->nativeLoader->loadFiles($fixturesFiles, $parameters, $objects);

        return $objectSet->getObjects();
    }
}
