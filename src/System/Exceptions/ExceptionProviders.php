<?php

/**
 * Part of Omega - Exceptions Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace System\Exceptions;

use DI\DependencyException;
use DI\NotFoundException;
use ErrorException;
use System\Application\Application;
use Throwable;

use function error_get_last;
use function error_reporting;
use function in_array;
use function ini_set;
use function php_sapi_name;
use function register_shutdown_function;
use function set_error_handler;
use function set_exception_handler;
use function str_repeat;

use const E_ALL;
use const E_COMPILE_ERROR;
use const E_CORE_ERROR;
use const E_DEPRECATED;
use const E_ERROR;
use const E_PARSE;
use const E_USER_DEPRECATED;

/**
 * HandlerExceptions class.
 *
 * The `HandlerExceptions` class is responsible for bootstrapping error and exception
 * handling within the Omega framework. It registers custom handlers for errors, exceptions,
 * and fatal shutdown events. Additionally, it integrates logging and ensures that deprecations
 * are properly processed.
 *
 * The class also manages PHP configuration settings related to error reporting and display,
 * ensuring that errors are handled consistently across different environments.
 *
 * @category  System
 * @package   Exceptions
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */
class ExceptionProviders
{
    /** @var Application Holds the current application instance, providing access to core services. */
    private Application $app;

    /** @var string|null A reserved memory buffer used to handle out-of-memory errors gracefully. */
    public static ?string $reserveMemory = null;

    /**
     * Initializes the application's error and exception handling.
     *
     * - Reserves memory for handling fatal errors.
     * - Enables full error reporting.
     * - Registers handlers for errors, exceptions, and shutdown events.
     * - Disables error display in non-testing environments.
     *
     * @param Application $app The application instance.
     * @return void
     * @throws DependencyException If a dependency cannot be resolved.
     * @throws NotFoundException If a required component is not found.
     */
    public function bootstrap(Application $app): void
    {
        self::$reserveMemory = str_repeat('x', 32_768);

        $this->app = $app;

        error_reporting(E_ALL);

        /* @phpstan-ignore-next-line */
        set_error_handler([$this, 'handleError']);

        set_exception_handler([$this, 'handleException']);

        register_shutdown_function([$this, 'handleShutdown']);

        if ('testing' !== $app->getEnvironment()) {
            ini_set('display_errors', 'Off');
        }
    }

    /**
     * Handles runtime errors and converts them into exceptions.
     *
     * - If the error is a deprecation warning, it is logged separately.
     * - If the error level is enabled in `error_reporting()`, it is thrown as an `ErrorException`.
     *
     * @param int    $level   The severity level of the error.
     * @param string $message The error message.
     * @param string $file    The file in which the error occurred.
     * @param ?int   $line    The line number where the error occurred.
     * @return void
     * @throws ErrorException If the error level matches the configured reporting level.
     */
    public function handleError(int $level, string $message, string $file = '', ?int $line = 0): void
    {
        if ($this->isDeprecation($level)) {
            $this->handleDeprecationError($message, $file, $line, $level);
        }

        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Handles and logs deprecation warnings.
     *
     * **Known Issue:**
     * - The method accepts `$file` and `$line` but does not use them.
     * - The `$level` parameter is redundant, as it is not used internally.
     *
     * @param string $message The deprecation warning message.
     * @param string $file    The file where the warning occurred.
     * @param int    $line    The line number of the warning.
     * @param int    $level   The severity level (currently unused).
     * @return void
     */
    private function handleDeprecationError(string $message, string $file, int $line, int $level): void
    {
        $this->log($level, $message);
    }

    /**
     * Handles uncaught exceptions.
     *
     * - Clears the reserved memory buffer.
     * - Logs the exception.
     * - If running in a web environment, it renders the error response.
     *
     * @param Throwable $th The uncaught exception.
     * @return void
     * @throws Throwable If the exception is not handled.
     */
    public function handleException(Throwable $th): void
    {
        self::$reserveMemory = null;

        $handler = $this->getHandler();
        $handler->report($th);
        if (php_sapi_name() !== 'cli') {
            $handler->render($this->app['request'], $th)->send();
        }
    }

    /**
     * Handles fatal shutdown events.
     *
     * - Clears the reserved memory buffer.
     * - Checks for a last error using `error_get_last()`.
     * - If a fatal error occurred, it is converted into an `ErrorException` and handled.
     *
     * @return void
     * @throws Throwable If a fatal error occurs.
     */
    public function handleShutdown(): void
    {
        self::$reserveMemory = null;
        $error               = error_get_last();
        if ($error && $this->isFatal($error['type'])) {
            $this->handleException(
                new ErrorException(
                    $error['message'],
                    0,
                    $error['type'],
                    $error['file'],
                    $error['line']
                )
            );
        }
    }

    /**
     * Logs a message if a logger service is available.
     *
     * @param int    $level   The log level.
     * @param string $message The log message.
     * @return bool True if logging was successful, false otherwise.
     */
    private function log(int $level, string $message): bool
    {
        if ($this->app->has('log')) {
            $this->app['log']->log($level, $message);

            return true;
        }

        return false;
    }

    /**
     * Retrieves the exception handler instance from the application container.
     *
     * @return Handler The exception handler instance.
     */
    private function getHandler(): Handler
    {
        return $this->app[Handler::class];
    }

    /**
     * Determines if the given error level corresponds to a deprecation warning.
     *
     * @param int $level The error level.
     * @return bool True if the level is `E_DEPRECATED` or `E_USER_DEPRECATED`, false otherwise.
     */
    private function isDeprecation(int $level): bool
    {
        return in_array($level, [E_DEPRECATED, E_USER_DEPRECATED]);
    }

    /**
     * Determines if the given error level corresponds to a fatal error.
     *
     * @param int $level The error level.
     * @return bool True if the level is `E_ERROR`, `E_CORE_ERROR`, `E_COMPILE_ERROR`, or `E_PARSE`, false otherwise.
     */
    private function isFatal(int $level): bool
    {
        return in_array($level, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE]);
    }
}
