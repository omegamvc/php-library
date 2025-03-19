<?php

/**
 * Part of Omega - Console Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace System\Console;

use Exception;
use System\Console\Style\Alert;
use System\Console\Style\Style;
use System\Console\Traits\TerminalTrait;

use function constant;
use function defined;
use function function_exists;
use function pcntl_signal;
use function posix_getgid;
use function posix_kill;
use function sapi_windows_set_ctrl_handler;

use const PHP_SAPI;
use const PHP_WINDOWS_EVENT_CTRL_C;

/**
 * Omega Framework - Console Helpers
 *
 * This file contains various helper functions for interacting with the terminal.
 * It provides functions for text styling, displaying alerts, handling user input,
 * retrieving terminal dimensions, and managing signal events.
 *
 * @category  System
 * @package   Console
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html  GPL V3.0+
 * @version   2.0.0
 */
if (!function_exists('style')) {
    /**
     * Render text with terminal style.
     *
     * This function applies the default terminal style to the provided text.
     * The text can then be modified further using method chaining.
     *
     * @param string $text Holds the text to render.
     * @return Style Return the styled text object.
     */
    function style(string $text): Style
    {
        return new Style($text);
    }
}

if (!function_exists('info')) {
    /**
     * Render an informational alert.
     *
     * This function renders a styled "info" alert message.
     *
     * @param string $text Holds the info message to display.
     * @return Style Return the styled text object.
     */
    function info(string $text): Style
    {
        return Alert::render()->info($text);
    }
}

if (!function_exists('warn')) {
    /**
     * Render a warning alert.
     *
     * This function renders a styled "warn" alert message.
     *
     * @param string $text Holds the warning message to display.
     * @return Style Return the styled text object.
     */
    function warn(string $text): Style
    {
        return Alert::render()->warn($text);
    }
}

if (!function_exists('fail')) {
    /**
     * Render a failure alert.
     *
     * This function renders a styled "fail" alert message.
     *
     * @param string $text Holds the fail message to display.
     * @return Style Return the styled text object.
     */
    function fail(string $text): Style
    {
        return Alert::render()->fail($text);
    }
}

if (!function_exists('ok')) {
    /**
     * Render a success alert.
     *
     * This function renders a styled "ok" (success) alert message.
     *
     * @param string $text Holds the ok message to display.
     * @return Style Return the styled text object.
     */
    function ok(string $text): Style
    {
        return Alert::render()->ok($text);
    }
}

if (!function_exists('option')) {
    /**
     * Prompt user for an option input.
     *
     * This function presents a list of options to the user and returns their selection.
     *
     * @param string|Style $title Holds the title or prompt to display to the user.
     * @param array<string, callable> $options Holds the list of options with associated callback functions.
     * @return mixed Return the selected option.
     * @throws Exception
     */
    function option(string|Style $title, array $options): mixed
    {
        return (new Prompt($title, $options))->option();
    }
}

if (!function_exists('select')) {
    /**
     * Prompt user for a selection input.
     *
     * This function presents a list of selectable options to the user and returns their selection.
     *
     * @param string|Style $title Holds the title or prompt to display to the user.
     * @param array<string, callable> $options Holds the list of options with associated callback functions.
     * @return mixed Return the selected option.
     * @throws Exception
     */
    function select(string|Style $title, array $options): mixed
    {
        return (new Prompt($title, $options))->select();
    }
}

if (!function_exists('text')) {
    /**
     * Prompt user for text input.
     *
     * This function prompts the user for text input and processes it using a provided callable.
     *
     * @param string|Style $title Holds the title or prompt to display to the user.
     * @param callable $callable Holds the function to process the input.
     * @return mixed Return the processed input.
     * @throws Exception
     */
    function text(string|Style $title, callable $callable): mixed
    {
        return (new Prompt($title))->text($callable);
    }
}

if (!function_exists('password')) {
    /**
     * Prompt user for password input.
     *
     * This function prompts the user for a password input and processes it using a provided callable.
     *
     * @param string|Style $title    Holds the title or prompt to display to the user.
     * @param callable     $callable Holds the function to process the input.
     * @param string       $mask     Holds the character to use as a mask for the password (default is an empty string).
     * @return mixed Return the processed input.
     */
    function password(string|Style $title, callable $callable, string $mask = ''): mixed
    {
        return (new Prompt($title))->password($callable, $mask);
    }
}

if (!function_exists('any_key')) {
    /**
     * Wait for any key press.
     *
     * This function waits for the user to press any key and then processes the input using a provided callable.
     *
     * @param string|Style $title    Holds the title or prompt to display to the user.
     * @param callable     $callable Holds the function to process the input.
     * @return mixed Return the processed input.
     */
    function any_key(string|Style $title, callable $callable): mixed
    {
        return (new Prompt($title))->anyKey($callable);
    }
}

if (!function_exists('width')) {
    /**
     * Get the terminal's width within a specified range.
     *
     * This function retrieves the current width of the terminal and ensures it falls within a specified range.
     * If the width is below the minimum or above the maximum, the closest valid value is returned.
     *
     * @param int $min Holds the minimum acceptable terminal width.
     * @param int $max Holds the maximum acceptable terminal width.
     * @return int Return the terminal width within the specified range.
     */
    function width(int $min, int $max): int
    {
        $terminal = new class {
            use TerminalTrait;

            /**
             * Retrieve the terminal width within a given range.
             *
             * This method uses the `TerminalTrait` to get the terminal's width and
             * ensure it falls within the provided limits.
             *
             * @param int $min Holds the minimum acceptable terminal width.
             * @param int $max Holds the maximum acceptable terminal width.
             * @return int Return the terminal width within the specified range.
             */
            public function width(int $min, int $max): int
            {
                return $this->getWidth($min, $max);
            }
        };

        return $terminal->width($min, $max);
    }
}

if (!function_exists('exit_prompt')) {
    /**
     * Register a prompt to exit when Ctrl+C is pressed.
     *
     * This function registers a handler to prompt the user for confirmation when they press Ctrl+C,
     * providing them with options to either confirm or cancel the action.
     *
     * @param string|Style $title Holds the title or prompt to display to the user.
     * @param array<string, callable> $options Holds the associative array of options and callback functions.
     * @return void
     * @throws Exception
     */
    function exit_prompt(string|Style $title, ?array $options = null): void
    {
        $signal = defined('SIGINT') ? constant('SIGINT') : 2;
        $options ??= [
            'yes' => static function () use ($signal) {
                if (function_exists('posix_kill') && function_exists('posix_getpid')) {
                    posix_kill(posix_getgid(), $signal);
                }

                exit(128 + $signal);
            },
            'no'  => fn () => null,
        ];

        if (function_exists('sapi_windows_set_ctrl_handler') && 'cli' === PHP_SAPI) {
            sapi_windows_set_ctrl_handler(static function (int $event) use ($title, $options) {
                if (PHP_WINDOWS_EVENT_CTRL_C === $event) {
                    (new Style())->out();
                    (new Prompt($title, $options, 'no'))->option();
                }
            });
        }

        if (function_exists('pcntl_signal')) {
            pcntl_signal($signal, $options['yes']);
        }
    }
}

if (!function_exists('remove_exit_prompt')) {
    /**
     * Remove ctrl-c handle.
     *
     * @return void
     */
    function remove_exit_prompt(): void
    {
        if (function_exists('sapi_windows_set_ctrl_handler') && 'cli' === PHP_SAPI) {
            sapi_windows_set_ctrl_handler(function (int $handler): void {
            }, false);
        }

        $signal  = defined('SIGINT') ? constant('SIGINT') : 2;
        $default = defined('SIG_DFL') ? constant('SIG_DFL') : 0;
        if (function_exists('pcntl_signal')) {
            pcntl_signal($signal, $default);
        }
    }
}
