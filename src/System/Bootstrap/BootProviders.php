<?php

declare(strict_types=1);

namespace System\Bootstrap;

use System\Application\Application;

class BootProviders
{
    public function bootstrap(Application $app): void
    {
        $app->bootProvider();
    }
}
