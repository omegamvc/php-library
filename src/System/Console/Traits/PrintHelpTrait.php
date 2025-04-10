<?php

declare(strict_types=1);

namespace System\Console\Traits;

use System\Console\Style\Style;
use System\Text\Str;

trait PrintHelpTrait
{
    /**
     * Print helper style option.
     *
     * @var array<string, string|int>
     */
    protected $printHelp = [
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
        $option_names =  array_keys($this->commandDescribes);

        $min_length = $this->printHelp['column-1-min-length'];
        foreach ($option_names as $name) {
            $arguments_length = 0;
            if (isset($this->commandRelation[$name])) {
                $arguments        = implode(' ', $this->commandRelation[$name]);
                $arguments_length = \strlen($arguments);
            }

            $length = \strlen($name) + $arguments_length;
            if ($length > $min_length) {
                $min_length = $length;
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
        $option_names =  array_keys($this->optionDescribes);

        $min_length = $this->printHelp['column-1-min-length'];
        foreach ($option_names as $name) {
            $length = \strlen($name);
            if ($length > $min_length) {
                $min_length = $length;
            }
        }

        foreach ($this->optionDescribes as $option => $describe) {
            $style->repeat(' ', $this->printHelp['margin-left']);

            $option_name = Str::fillEnd($option, ' ', $min_length + 8);
            $style->push($option_name)->textDim();

            $style->push($describe);
            $style->newLines();
        }

        return $style;
    }
}
