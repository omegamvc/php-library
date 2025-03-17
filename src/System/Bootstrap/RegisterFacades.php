<?php

/**
 * Part of Omega - Bootstrap Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.0
 */

declare(strict_types=1);

namespace System\Bootstrap;

use DI\DependencyException;
use DI\NotFoundException;
use System\Application\Application;
use System\Support\Facades\Facade;

/**
 * Sets up facades to provide a static interface to services within the application.
 *
 * @category  System
 * @package   Bootstrap
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.
 */
class RegisterFacades
{
    /**
     * Initializes facades by setting the base application instance in the Facade class, enabling
     * the use of static method calls for various services.
     *
     * @param Application $app Holds the current Application instance.
     * @return void
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found
     */
    public function bootstrap(Application $app): void
    {
        Facade::setFacadeBase($app);
    }
}
