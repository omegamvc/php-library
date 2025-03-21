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

namespace System\Console\Style;

use System\Console\Traits\CommandTrait;

use function array_key_exists;
use function array_keys;
use function array_map;
use function array_merge;
use function call_user_func;
use function call_user_func_array;
use function ceil;
use function str_pad;
use function str_repeat;
use function str_replace;

use const PHP_EOL;

/**
 * Class for creating and managing a progress bar in the console.
 *
 * This class provides a simple way to display a progress bar in the terminal/console, showing
 * the current progress based on a given maximum value. The progress bar is customizable through
 * templates and binding functions. It also allows for a completion callback once the task is complete.
 *
 * The progress bar dynamically updates with each tick, providing feedback about the task's progress.
 *
 * @category   System
 * @package    Console
 * @subpackage Style
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
class ProgressBar
{
    use CommandTrait;

    /**
     * Template for displaying the progress bar.
     *
     * This property holds the template string that defines how the progress bar is displayed. It can
     * include placeholders like ':progress' for the bar itself and ':percent' for the percentage of completion.
     *
     * @var string
     */
    private string $template;

    /**
     * Current progress value.
     *
     * This property stores the current progress, which is updated as the task progresses. It is compared
     * against the maximum value to determine when the task is complete.
     *
     * @var int
     */
    public int $current = 0;

    /**
     * Maximum progress value.
     *
     * This property defines the maximum value for the progress bar. Once the current progress reaches this
     * value, the task is considered complete.
     *
     * @var int
     */
    public int $maks = 1;

    /**
     * Holds the string representation of the current progress.
     *
     * This property stores the updated string of the progress bar that is displayed to the user. It is
     * generated by the `__toString` method and is replaced in the terminal/console with each tick.
     *
     * @var string
     */
    private string $progress;

    /**
     * Callback function when the task is complete.
     *
     * This property holds a callback function that is executed when the progress bar reaches the maximum value.
     * It returns a string which represents the completed task status.
     *
     * @var callable(): string
     */
    public mixed $complete;

    /**
     * Custom bindings for progress bar placeholders.
     *
     * This property stores the custom binding functions that can be used to replace placeholders in the template.
     * Each function takes the current and maximum progress values as arguments and returns a string to be inserted
     * in the template.
     *
     * @var array<callable(int, int): string>
     */
    private array $binds;

    /**
     * Static custom bindings for progress bar placeholders.
     *
     * This static property holds the custom bindings shared across all instances of the progress bar. It can be
     * modified globally to affect the template bindings for all progress bars.
     *
     * @var array<callable(int, int): string>
     */
    public static array $customBinds = [];

    /**
     * Initializes the progress bar with a given template and custom bindings.
     *
     * The constructor accepts an optional template and an array of bindings. The default template
     * is ':progress :percent', and the default bindings display the progress and percentage. The
     * method sets up the progress bar and binds the necessary template values.
     *
     * @param string $template The template for displaying the progress (default: ':progress :percent').
     * @param array<callable(int, int): string> $binds The bindings for customizing the progress display.
     */
    public function __construct(string $template = ':progress :percent', array $binds = [])
    {
        $this->progress = '';
        $this->template = $template;
        $this->complete = fn (): string => $this->complete();
        $this->binding($binds);
    }

    /**
     * Converts the progress bar to a string representation.
     *
     * This method assembles the progress bar as a string using the current progress, maximum value,
     * and the specified template. It replaces the placeholders in the template with the actual progress values.
     *
     * @return string The string representation of the progress bar.
     */
    public function __toString()
    {
        $binds = array_map(function ($bind) {
            return call_user_func_array($bind, [
                $this->current,
                $this->maks,
            ]);
        }, $this->binds);

        return str_replace(array_keys($binds), $binds, $this->template);
    }
    /**public function __toString()
    {
        $binds = [];
        foreach ($this->binds as $key => $bind) {
            $binds[$key] = call_user_func_array($bind, [
                $this->current,
                $this->maks,
            ]);
        }

        return str_replace(array_keys($binds), $binds, $this->template);
    }*/

    /**
     * Updates the progress bar by one tick.
     *
     * This method increments the current progress and updates the displayed progress bar in the console.
     * When the task is complete, it displays a completion message and clears the progress bar.
     *
     * @return void
     */
    public function tick(): void
    {
        $this->progress = (string) $this;
        (new Style())->replace($this->progress);

        if ($this->current + 1 > $this->maks) {
            $complete = (string) call_user_func($this->complete);
            (new Style())->clear();
            (new Style())->replace($complete . PHP_EOL);
        }
    }

    /**
     * Customizes the progress bar's template and updates it by one tick.
     *
     * This method allows the user to specify a new template and bindings for the progress bar. It then
     * updates the progress display accordingly. Once the task reaches completion, a completion message is shown.
     *
     * @param string $template The new template for displaying the progress (default: ':progress :percent').
     * @param array<callable(int, int): string> $binds The bindings for customizing the progress display.
     */
    public function tickWith(string $template = ':progress :percent', array $binds = []): void
    {
        $this->template = $template;
        $this->binding($binds);
        $this->progress = (string) $this;
        (new Style())->replace($this->progress);

        if ($this->current + 1 > $this->maks) {
            $complete = (string) call_user_func($this->complete);
            (new Style())->clear();
            (new Style())->replace($complete . PHP_EOL);
        }
    }

    /**
     * Generates the progress bar string based on the current and maximum values.
     *
     * This private method creates the visual representation of the progress bar. It calculates the
     * number of ticks based on the current progress, fills the bar with '=' characters, and adds a
     * '>' or '=' depending on whether the task is complete.
     *
     * @param int $current The current progress value.
     * @param int $maks The maximum progress value.
     * @return string The progress bar as a string.
     */
    private function progress(int $current, int $maks): string
    {
        $length = 20;
        $tick   = (int) ceil($current * ($length / $maks)) - 1;
        $head   = $current === $maks ? '=' : '>';
        $bar    = str_repeat('=', $tick) . $head;
        $left   = '-';

        return '[' . str_pad($bar, $length, $left) . ']';
    }

    /**
     * Binds the custom template placeholders to specific progress display functions.
     *
     * This method allows the user to bind custom functions to placeholders within the template. It ensures
     * that the ':progress' and ':percent' placeholders are always properly bound to display the progress
     * and percentage, respectively.
     *
     * @param array<callable(int, int): string> $binds The custom bindings for the progress template.
     * @return void
     */
    public function binding(array $binds): void
    {
        $binds = array_merge($binds, self::$customBinds);
        if (false === array_key_exists(':progress', $binds)) {
            $binds[':progress'] =  fn ($current, $maks): string => $this->progress($current, $maks);
        }

        if (false === array_key_exists(':percent', $binds)) {
            $binds[':percent'] =  fn ($current, $maks): string => ceil(($current / $maks) * 100) . '%';
        }
        $this->binds    = $binds;
    }

    /**
     * Returns the final progress state after completion.
     *
     * This private method generates the final string representation of the progress bar
     * after the task is complete.
     *
     * @return string The completed progress bar string.
     */
    private function complete(): string
    {
        return $this->progress;
    }
}
