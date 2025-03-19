<?php

/**
 * Part of Omega - Container Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace System\Container\ServiceProvider;

use DI\DependencyException;
use DI\NotFoundException;
use System\Application\Application;

/**
 * Registers service providers, making them available within the application lifecycle.
 *
 * @category   System
 * @package    Container
 * @subpackage ServiceProvider
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class RegisterProviders
{
    /**
     * Registers service providers.
     *
     * Ensuring that all necessary services are available before bootstrapping.
     *
     * @param Application $app Holds the current Application instance.
     * @return void
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found
     */
    public function bootstrap(Application $app): void
    {
        $app->registerProvider();
    }
}
