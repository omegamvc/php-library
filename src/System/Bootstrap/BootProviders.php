<?php

/**
 * Part of Omega - Bootstrap Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace System\Bootstrap;

use DI\DependencyException;
use DI\NotFoundException;
use System\Application\Application;

/**
 * Triggers the boot process for service providers registered in the application.
 *
 * @category  System
 * @package   Bootstrap
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class BootProviders
{
    /**
     * Boots the registered service providers in the application.
     *
     * @param Application $app Holds the current Application instance.
     * @return void
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found
     */
    public function bootstrap(Application $app): void
    {
        $app->bootProvider();
    }
}
