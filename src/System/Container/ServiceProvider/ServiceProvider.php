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

use Closure;
use System\File\UploadFile;
use System\Http\Request;
use System\Validator\Validator;

/**
 * Core service provider for the application container.
 *
 * This class extends `AbstractServiceProvider` and is responsible for registering
 * additional functionalities into the application. Specifically, it enhances
 * the `Request` class by introducing macros for validation and file uploads.
 *
 * @category   System
 * @package    Container
 * @subpackage ServiceProvider
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
class ServiceProvider extends AbstractServiceProvider
{
    /**
     * {@inheritDoc}
     */
    public function register(): void
    {
        Request::macro(
            'validate',
            fn (?Closure $rule = null, ?Closure $filter = null) => Validator::make($this->{'all'}(), $rule, $filter)
        );

        Request::macro(
            'upload',
            function ($fileName) {
                $files = $this->{'getFile'}();

                return new UploadFile($files[$fileName]);
            }
        );
    }
}
