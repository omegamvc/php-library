<?php

declare(strict_types=1);

namespace System\Support\Bootstrap;

use System\Application\Application;
use System\Support\Facades\Facade;

class RegisterFacades
{
    public function bootstrap(Application $app): void
    {
        Facade::setFacadeBase($app);
    }
}
