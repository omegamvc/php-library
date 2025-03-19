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
use System\Console\Style\Style;

use function array_key_exists;
use function array_keys;
use function array_merge;
use function array_pop;
use function array_values;
use function chr;
use function fgets;
use function fwrite;
use function join;
use function readline_callback_handler_install;
use function stream_get_contents;
use function trim;

use const STDIN;
use const STDOUT;

/**
 * Prompt class.
 *
 * The `Prompt` class is used to display interactive prompts in the console.
 *
 * It supports various types of input options, such as text input, selection from a list,
 * password masking, and waiting for any key press.
 *
 * @category  System
 * @package   Console
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 *
 * @property string $_ Get argument name
 */
class Prompt
{
    /**
     * @var string|Style The title or prompt that will be displayed to the user.
     */
    private string|Style $title;

    /**
     * @var array<string, callable> A list of options where the key is the input string and the value is a callable to execute for that option.
     */
    private array $options;

    /**
     * @var string The default option to execute if no valid input is provided.
     */
    private string $default;

    /**
     * @var string[]|Style[] A list of selection options that can be presented to the user.
     */
    private array $selection;

    /**
     * Prompt constructor.
     *
     * @param string|Style            $title   The title or prompt to display.
     * @param array<string, callable> $options A set of options, each associated with a callable.
     * @param string                  $default The default option to select if the user doesn't provide input.
     * @return void
     */
    public function __construct(string|Style $title, array $options = [], string $default = '')
    {
        $this->title     = $title;
        $this->options   = array_merge(['' => fn () => false], $options);
        $this->default   = $default;
        $this->selection = array_keys($options);
    }

    /**
     * Reads user input from standard input (STDIN).
     *
     * @return string The user's input.
     * @throws Exception If input cannot be read.
     */
    private function getInput(): string
    {
        $input = fgets(STDIN);

        if ($input === false) {
            throw new Exception('Cant read input');
        }

        return trim($input);
    }

    /**
     * Sets the available selection options to present to the user.
     *
     * @param string[]|Style[] $selection A new list of selection options.
     * @return self
     */
    public function selection(array $selection): self
    {
        $this->selection = $selection;

        return $this;
    }

    /**
     * Displays the prompt and options, and waits for the user to input a selection.
     * If the input is valid, it executes the corresponding callable option.
     * Otherwise, it defaults to the predefined option.
     *
     * @return mixed The result of the executed callable for the selected option.
     * @throws Exception If input cannot be read.
     */
    public function option(): mixed
    {
        $style = new Style();
        $style->push($this->title)->push(' ');
        foreach ($this->selection as $option) {
            if ($option instanceof Style) {
                $style->tap($option);
            } else {
                $style->push("{$option} ");
            }
        }

        $style->out();
        $input = $this->getInput();
        if (array_key_exists($input, $this->options)) {
            return ($this->options[$input])();
        }

        return ($this->options[$this->default])();
    }

    /**
     * Displays the prompt with a numbered list of options and waits for the user to select one by its number.
     * If the selection is valid, it executes the corresponding callable option.
     * Otherwise, it defaults to the predefined option.
     *
     * @return mixed The result of the executed callable for the selected option.
     * @throws Exception If input cannot be read.
     */
    public function select(): mixed
    {
        $style = new Style();
        $style->push($this->title);
        $i = 1;
        foreach ($this->selection as $option) {
            if ($option instanceof Style) {
                $style->tap($option);
            } else {
                $style->newLines()->push("[{$i}] {$option}");
            }
            $i++;
        }

        $style->out();
        $input  = $this->getInput();
        $select = array_values($this->options);

        if (array_key_exists($input, $select)) {
            return ($select[$input])();
        }

        return ($this->options[$this->default])();
    }

    /**
     * Displays the prompt and waits for the user to input text.
     * The input is processed by the provided callable.
     *
     * @param callable $callable A callable to process the user input.
     * @return mixed The result of the executed callable.
     * @throws Exception If input cannot be read.
     */
    public function text(callable $callable): mixed
    {
        (new Style($this->title))->out();

        return ($callable)($this->getInput());
    }

    /**
     * Displays the prompt and waits for the user to input a password.
     * The input is masked with the specified character and processed by the provided callable.
     *
     * @param callable $callable A callable to process the password input.
     * @param string   $mask     A character to mask the password input (default is empty).
     * @return mixed The result of the executed callable with the password input.
     */
    public function password(callable $callable, string $mask = ''): mixed
    {
        (new Style($this->title))->out();

        $userLine = [];
        readline_callback_handler_install('', function () {
        });
        while (true) {
            $keystroke = stream_get_contents(STDIN, 1);

            switch (ord($keystroke)) {
                case 10:
                    break 2;

                case 127:
                    array_pop($userLine);
                    fwrite(STDOUT, chr(8));
                    fwrite(STDOUT, "\033[0K");
                    break;

                default:
                    $userLine[] = $keystroke;
                    fwrite(STDOUT, $mask);
                    break;
            }
        }

        return ($callable)(join($userLine));
    }

    /**
     * Waits for any key press from the user and then executes the provided callable with the key input.
     *
     * @param callable $callable A callable to process the key input.
     * @return mixed The result of the executed callable with the key input.
     */
    public function anyKey(callable $callable): mixed
    {
        $prompt = (string) $this->title;
        readline_callback_handler_install($prompt, function () {
        });
        $keystroke = stream_get_contents(STDIN, 1);

        return ($callable)($keystroke);
    }
}
