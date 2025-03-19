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

namespace System\Console\Traits;

use System\Console\Style\Style;
use System\Text\Str;

use function array_keys;
use function implode;
use function strlen;

/**
 * The `PrintHelpTrait` provides helper methods for printing command descriptions and options
 * in a structured format within the console.
 *
 * This trait defines styling rules for command and option output, ensuring proper alignment
 * and readability. It calculates the necessary column widths dynamically and applies consistent
 * spacing for improved visual clarity.
 *
 * @category   System
 * @package    Console
 * @subpackage Traits
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
trait PrintHelpTrait
{
    /**
     * Configuration for help output styling.
     *
     * This array defines the left margin and the minimum column width
     * for command and option descriptions.
     *
     * @var array<string, string|int>
     */
    protected array $printHelp = [
        'margin-left'         => 12,
        'column-1-min-length' => 24,
    ];

    /**
     * Print command descriptions with formatted styling.
     *
     * This method aligns and styles command names along with their descriptions,
     * ensuring a readable and structured output.
     *
     * @param Style $style The style instance used for formatting.
     * @return Style The modified style instance.
     */
    public function printCommands(Style $style): Style
    {
        $option_names =  array_keys($this->commandDescribes);

        $minLength = $this->printHelp['column-1-min-length'];
        foreach ($option_names as $name) {
            $argumentsLength = 0;
            if (isset($this->commandRelation[$name])) {
                $arguments       = implode(' ', $this->commandRelation[$name]);
                $argumentsLength = strlen($arguments);
            }

            $length = strlen($name) + $argumentsLength;
            if ($length > $minLength) {
                $minLength = $length;
            }
        }

        foreach ($this->commandDescribes as $option => $describe) {
            $arguments = '';
            if (isset($this->commandRelation[$option])) {
                $arguments = implode(' ', $this->commandRelation[$option]);
                $arguments = ' ' . $arguments;
            }

            $style->repeat(' ', $this->printHelp['margin-left']);

            $style->push($option)->textGreen();
            $style->push($arguments)->textDim();

            $range = $minLength - (strlen($option) + strlen($arguments));
            $style->repeat(' ', $range + 8);

            $style->push($describe);
            $style->newLines();
        }

        return $style;
    }

    /**
     * Print option descriptions with formatted styling.
     *
     * This method ensures that all option names are properly aligned and styled,
     * making them easy to read in the console.
     *
     * @param Style $style The style instance used for formatting.
     * @return Style The modified style instance.
     */
    public function printOptions(Style $style): Style
    {
        $optionNames =  array_keys($this->optionDescribes);
        $minLength   = $this->printHelp['column-1-min-length'];

        foreach ($optionNames as $name) {
            $length = strlen($name);
            if ($length > $minLength) {
                $minLength = $length;
            }
        }

        foreach ($this->optionDescribes as $option => $describe) {
            $style->repeat(' ', $this->printHelp['margin-left']);

            $optionName = Str::fillEnd($option, ' ', $minLength + 8);
            $style->push($optionName)->textDim();

            $style->push($describe);
            $style->newLines();
        }

        return $style;
    }
}
