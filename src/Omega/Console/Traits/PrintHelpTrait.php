<?php

declare(strict_types=1);

namespace Omega\Console\Traits;

use Omega\Console\Style\Style;
use Omega\Text\Str;

trait PrintHelpTrait
{
    /**
     * Print helper style option.
     *
     * @var array<string, string|int>
     */
    protected $print_help = [
        'margin-left'         => 12,
        'column-1-min-length' => 24,
    ];

    /**
     * Print argument describe using style console.
     *
     * @return Style
     */
    public function printCommands(Style $style)
    {
        $option_names =  array_keys($this->command_describes);

        $min_length = $this->print_help['column-1-min-length'];
        foreach ($option_names as $name) {
            $arguments_lenght = 0;
            if (isset($this->command_relation[$name])) {
                $arguments        = implode(' ', $this->command_relation[$name]);
                $arguments_lenght = \strlen($arguments);
            }

            $length = \strlen($name) + $arguments_lenght;
            if ($length > $min_length) {
                $min_length = $length;
            }
        }

        foreach ($this->command_describes as $option => $describe) {
            $arguments = '';
            if (isset($this->command_relation[$option])) {
                $arguments = implode(' ', $this->command_relation[$option]);
                $arguments = ' ' . $arguments;
            }

            $style->repeat(' ', $this->print_help['margin-left']);

            $style->push($option)->textGreen();
            $style->push($arguments)->textDim();

            $range = $min_length - (\strlen($option) + \strlen($arguments));
            $style->repeat(' ', $range + 8);

            $style->push($describe);
            $style->newLines();
        }

        return $style;
    }

    /**
     * Print option describe using style console.
     *
     * @return Style
     */
    public function printOptions(Style $style)
    {
        $option_names =  array_keys($this->option_describes);

        $min_length = $this->print_help['column-1-min-length'];
        foreach ($option_names as $name) {
            $length = \strlen($name);
            if ($length > $min_length) {
                $min_length = $length;
            }
        }

        foreach ($this->option_describes as $option => $describe) {
            $style->repeat(' ', $this->print_help['margin-left']);

            $option_name = Str::fillEnd($option, ' ', $min_length + 8);
            $style->push($option_name)->textDim();

            $style->push($describe);
            $style->newLines();
        }

        return $style;
    }
}
