<?php

declare(strict_types=1);

namespace System\Support\Bootstrap;

use System\Application\Application;
use System\Container\Exception\DependencyResolutionException;
use System\Container\Exception\ServiceNotFoundException;

class BootProviders
{
    /**
     * @throws ServiceNotFoundException
     * @throws DependencyResolutionException
     */
    public function bootstrap(Application $app): void
    {
        $app->bootProvider();
    }
}
