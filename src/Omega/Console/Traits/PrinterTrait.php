<?php

declare(strict_types=1);

namespace Omega\Console\Traits;

use Omega\Console\Style\Decorate;

use function chr;
use function implode;
use function str_repeat;

trait PrinterTrait
{
    /**
     * Run commandline text rule.
     *
     * @param array<int, string|int> $rule
     * @param string|int $text
     * @param bool $reset
     * @param array<int, string|int> $resetRule
     * @return string
     */
    protected function rules(
        array $rule,
        string|int $text,
        bool $reset = true,
        array $resetRule = [Decorate::RESET]
    ): string {
        $stringRules      = implode(';', $rule);
        $stringResetRules = implode(';', $resetRule);

        return $this->rule($stringRules, $text, $reset, $stringResetRules);
    }

    /**
     * Run color code.
     *
     * @param int|string $rule
     * @param string     $text
     * @param bool       $reset
     * @param int|string $resetRule
     * @return string
     */
    protected function rule(
        int|string $rule,
        string $text,
        bool $reset = true,
        int|string $resetRule = Decorate::RESET
    ): string {
        $rule      = chr(27) . '[' . $rule . 'm' . $text;
        $resetRule = chr(27) . '[' . $resetRule . 'm';

        return $reset
            ? $rule . $resetRule
            : $rule;
    }

    /**
     * Print new line x times.
     *
     * @deprecated
     *
     * @param int $count
     * @return void
     */
    protected function print_n(int $count = 1): void
    {
        echo str_repeat("\n", $count);
    }

    /**
     * Print tab x times.
     *
     * @deprecaated
     *
     * @param int $count
     * @return void
     */
    protected function print_t(int $count = 1): void
    {
        echo str_repeat("\t", $count);
    }

    /**
     * New line.
     *
     * @param int $count
     * @return string
     */
    protected function newLine(int $count = 1): string
    {
        return str_repeat("\n", $count);
    }

    /**
     * Tabs
     *
     * @param int $count
     * @return string
     */
    protected function tabs(int $count = 1): string
    {
        return str_repeat("\t", $count);
    }

    /**
     * Clear from the cursor position to the beginning of the line.
     *
     * @deprecated
     *
     * @return void
     */
    protected function clearCursor(): void
    {
        echo chr(27) . '[1K';
    }

    /**
     * Clear everything on the line.
     *
     * @deprecated
     *
     * @return void
     */
    protected function clear_line(): void
    {
        echo chr(27) . '[2K';
    }

    /**
     * Replace single line output to new string.
     *
     * @param string $replace
     * @param int    $line
     * @return void
     */
    protected function replaceLine(string $replace, int $line = -1): void
    {
        $this->moveLine($line);
        echo chr(27) . "[K\r" . $replace;
    }

    /**
     * Remove / reset current line to empty.
     *
     * @param int $line
     * @return void
     */
    protected function clearLine(int $line = -1): void
    {
        $this->moveLine($line);
        $this->replaceLine('');
    }

    /**
     * Move to line (start from bottom).
     *
     * @param int $line
     * @return void
     */
    protected function moveLine(int $line): void
    {
        echo chr(27) . "[{$line}A";
    }
}
