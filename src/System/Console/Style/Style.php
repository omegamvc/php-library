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

use System\Console\Output\OutputInterface;
use System\Console\Style\Color\BackgroundColor;
use System\Console\Style\Color\ForegroundColor;
use System\Console\Traits\CommandTrait;
use System\Text\Str;

use function max;
use function method_exists;
use function preg_replace;
use function str_repeat;
use function strlen;
use function strtolower;
use function System\Text\text;

use const PHP_EOL;

/**
 * The `Style` class provides a way to apply terminal text styling,
 * including colors, decorations, and raw terminal codes.
 *
 * This class allows chaining methods to modify text appearance,
 * such as setting foreground and background colors, applying bold or underline styles,
 * and handling raw ANSI escape codes. The formatted text can be printed to the terminal
 * or retrieved as a string.
 *
 * The class also supports method invocation to reset styles and apply new ones dynamically.
 *
 * @category   System
 * @package    Console
 * @subpackage Style
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 *
 * @method self textRed()
 * @method self textYellow()
 * @method self textBlue()
 * @method self textGreen()
 * @method self textDim()
 * @method self textMagenta()
 * @method self textCyan()
 * @method self textLightGray()
 * @method self textDarkGray()
 * @method self textLightGreen()
 * @method self textLightYellow()
 * @method self textLightBlue()
 * @method self textLightMagenta()
 * @method self textLightCyan()
 * @method self textWhite()
 * @method self bgRed()
 * @method self bgYellow()
 * @method self bgBlue()
 * @method self bgGreen()
 * @method self bgMagenta()
 * @method self bgCyan()
 * @method self bgLightGray()
 * @method self bgDarkGray()
 * @method self bgLightGreen()
 * @method self bgLightYellow()
 * @method self bgLightBlue()
 * @method self bgLightMagenta()
 * @method self bgLightCyan()
 * @method self bgWhite()
 */
class Style
{
    use CommandTrait;

    /** @var array<int, int> Holds the ANSI escape codes for the current style rules.
    * @noinspection PhpPrivateFieldCanBeLocalVariableInspection
    */
    private array $rules = [];

    /** @var array<int, array<int, string|int>> Stores raw terminal styling rules, including colors and decorations. */
    private array $rawRules = [];

    /** @var array<int, int> Defines the reset rules to clear applied styles. */
    private array $resetRules = [Decorate::RESET];

    /** @var array<int, int> Defines the foreground text color rule. */
    private array $textColorRule = [Decorate::TEXT_DEFAULT];

    /** @var array<int, int> Defines the background color rule. */
    private array $bgColorRule = [Decorate::BG_DEFAULT];

    /** @var array<int, int> Stores text decoration rules (e.g., bold, underline). */
    private array $decorateRules = [];

    /** @var string The text content to be styled. */
    private string $text;

    /** @var int The length of the text. */
    private int $length;

    /** @var string A reference string to prepend to the styled text. */
    private string $ref = '';

    /** @var OutputInterface|null Output interface for writing styled text to a stream. */
    private ?OutputInterface $output = null;

    /**
    * Initializes a new instance of the Style class.
    *
    * @param string|int $text The text to be styled.
    * @return void
    */
    public function __construct(string|int $text = '')
    {
        $this->text   = $text;
        $this->length = strlen((string) $text);
    }

    /**
    * Reinitialized the instance with a new text value.
    *
    * @param string|int $text The new text to style.
    * @return self
    */
    public function __invoke(string|int $text): self
    {
        $this->text   = $text;
        $this->length = strlen((string) $text);

        return $this->flush();
    }

    /**
    * Converts the styled text to a string with ANSI formatting.
    *
    * @return string Return the styled text with ANSI formatting
    */
    public function __toString(): string
    {
        return $this->toString($this->text, $this->ref);
    }

    /**
    * Dynamically handles method calls for styling shortcuts.
    *
    * @param string $name The method name.
    * @param array<int, mixed> $arguments The arguments passed to the method.
    * @return self
    */
    public function __call(string $name, array $arguments): self
    {
        if (method_exists($this, $name)) {
            $constant = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name));

            if (Str::startsWith($name, 'text')) {
                $constant              = 'TEXT' . text($constant)->upper()->slice(4);
                $this->textColorRule = [Decorate::getConst($constant)];
            }

            if (Str::startsWith($name, 'bg')) {
                $constant            =  'BG' . text($constant)->upper()->slice(2);
                $this->bgColorRule = [Decorate::getConst($constant)];
            }

            return $this;
        }

        $constant = text($name)->upper();
        if ($constant->startsWith('TEXT_')) {
            $constant->slice(5);
            $this->textColor(Colors::hexText(ColorVariant::getConst($constant->__toString())));
        }

        if ($constant->startsWith('BG_')) {
            $constant->slice(3);
            $this->bgColor(Colors::hexBg(ColorVariant::getConst($constant->__toString())));
        }

        return $this;
    }

    /**
    * Applies the current styling rules and returns the formatted string.
    *
    * @param string $text The text to style.
    * @param string $ref An optional prefix to prepend to the text.
    * @return string
    */
    public function toString(string $text, string $ref = ''): string
    {
        // make sure not push empty text
        if ($text == '' && $ref == '') {
            return '';
        }

        // flush
        $this->rules = [];

        // font color
        foreach ($this->textColorRule as $textColor) {
            $this->rules[] = $textColor;
        }

        // bg color
        foreach ($this->bgColorRule as $bgColor) {
            $this->rules[] = $bgColor;
        }

        // decorate
        foreach ($this->decorateRules as $decorate) {
            $this->rules[] = $decorate;
        }

        // raw
        foreach ($this->rawRules as $raws) {
            foreach ($raws as $raw) {
                $this->rules[] = $raw;
            }
        }

        return $ref . $this->rules($this->rules, $text, true, $this->resetRules);
    }

    /**
    * Resets all styling attributes to their default values.
    *
    * @return self
    */
    public function flush(): self
    {
        $this->textColorRule  = [Decorate::TEXT_DEFAULT];
        $this->bgColorRule    = [Decorate::BG_DEFAULT];
        $this->decorateRules  = [];
        $this->resetRules     = [Decorate::RESET];
        $this->rawRules       = [];
        $this->ref            = '';

        return $this;
    }

    /**
    * Sets a reference string to be prepended to the styled text.
    *
    * @param string $textReference The reference string.
    * @return self
    */
    private function ref(string $textReference): self
    {
        $this->ref = $textReference;

        return $this;
    }

    /**
    * Appends new text while preserving the current style.
    *
    * @param string|int $text The text to append.
    * @return self
    */
    public function push(string|int $text): self
    {
        $ref        = $this->toString($this->text, $this->ref);
        $this->text = $text;
        $this->length += strlen((string) $text);

        return $this->flush()->ref($ref);
    }

    /**
    * Merges another Style instance into this one.
    *
    * @param Style $style The Style instance to merge.
    * @return self
    */
    public function tap(Style $style): self
    {
        $this->ref           = $this->toString($this->text, $this->ref) . $style->toString($style->ref);
        $this->text          = $style->text;
        $this->textColorRule = $style->textColorRule;
        $this->bgColorRule   = $style->bgColorRule;
        $this->decorateRules = $style->decorateRules;
        $this->resetRules    = $style->resetRules;
        $this->rawRules      = $style->rawRules;

        $this->length += $style->length;

        return $this;
    }

    /**
    * Gets the length of the styled text.
    *
    * @return int
    */
    public function length(): int
    {
        return $this->length;
    }

    /**
    * Outputs the styled text to the terminal.
    *
    * @param bool $newLine Whether to append a newline.
    * @return void
    */
    public function out(bool $newLine = true): void
    {
        $out = $this . ($newLine ? PHP_EOL : null);

        echo $out;
    }

    /**
    * Outputs the styled text if the given condition is true.
    *
    * @param bool $condition If true, the text is printed.
    * @param bool $newLine Whether to append a newline.
    * @return void
    */
    public function outIf(bool $condition, bool $newLine = true): void
    {
        if ($condition) {
            $out = $this . ($newLine ? PHP_EOL : null);

            echo $out;
        }
    }

    /**
    * Outputs the styled text and resets the instance.
    *
    * @return self
    */
    public function yield(): self
    {
        echo $this;
        $this->text   = '';
        $this->length = 0;
        $this->flush();

        return $this;
    }

    /**
    * Writes the styled text to the output stream.
    *
    * @param bool $newLine Whether to append a newline.
    * @return void
    */
    public function write(bool $newLine = true): void
    {
        $out = $this . ($newLine ? PHP_EOL : null);

        $this->output?->write($out);
    }

    /**
    * Clears the current terminal line.
    *
    * @param int $line The line number to clear.
    * @return void
    */
    public function clear(int $line = -1): void
    {
        $this->clearLine($line);
    }

    /**
    * Replaces the current terminal line with new text.
    *
    * @param string $text The new text.
    * @param int $line The line number to replace.
    * @return void
    */
    public function replace(string $text, int $line = -1): void
    {
        $this->replaceLine($text, $line);
    }

    /**
    * Sets the output stream for styled text.
    *
    * @param OutputInterface $output The output stream.
    * @return self
    */
    public function setOutput(OutputInterface $output): self
    {
        $this->output = $output;

        return $this;
    }

    /**
    * Resets text decorations without affecting colors.
    *
    * @return self
    */
    public function resetDecorate(): self
    {
        $this->resetRules = [Decorate::RESET];

        return $this;
    }

    /**
    * Applies bold styling to the text.
    *
    * @return self
    */
    public function bold(): self
    {
        $this->decorateRules[] = Decorate::BOLD;
        $this->resetRules[]    = Decorate::RESET_BOLD;

        return $this;
    }

    /**
    * Applies underline styling to the text.
    *
    * @return self
    */
    public function underline(): self
    {
        $this->decorateRules[] = Decorate::UNDERLINE;
        $this->resetRules[]    = Decorate::RESET_UNDERLINE;

        return $this;
    }

    /**
    * Applies blinking effect to the text.
    *
    * @return self
    */
    public function blink(): self
    {
        $this->decorateRules[] = Decorate::BLINK;
        $this->resetRules[]    = Decorate::RESET_BLINK;

        return $this;
    }

    /**
    * Applies reversed/inverted colors to the text.
    *
    * @return self
    */
    public function reverse(): self
    {
        $this->decorateRules[] = Decorate::REVERSE;
        $this->decorateRules[] = Decorate::RESET_REVERSE;

        return $this;
    }

    /**
    * Hides the text.
    *
    * @return self
    */
    public function hidden(): self
    {
        $this->decorateRules[] = Decorate::HIDDEN;
        $this->resetRules[]    = Decorate::RESET_HIDDEN;

        return $this;
    }

    /**
    * Adds a raw ANSI escape code.
    *
    * @param RuleInterface|string $raw The raw terminal code.
    * @return self
    */
    public function raw(RuleInterface|string $raw): self
    {
        if ($raw instanceof ForegroundColor) {
            $this->textColorRule = $raw->get();

            return $this;
        }

        if ($raw instanceof BackgroundColor) {
            $this->bgColorRule = $raw->get();

            return $this;
        }

        $this->rawRules[] = [$raw];

        return $this;
    }

    /**
    * Sets the reset rules for clearing applied styles.
    *
    * @param int[] $reset The reset rules to apply.
    * @return self
    */
    public function rawReset(array $reset): self
    {
        $this->resetRules = $reset;

        return $this;
    }

    /**
    * Sets the text color.
    *
    * @param ForegroundColor|string $color The foreground color.
    * @return self
    */
    public function textColor(ForegroundColor|string $color): self
    {
        $this->textColorRule = $color instanceof ForegroundColor
            ? $color->get()
            : Colors::hexText($color)->get()
        ;

        return $this;
    }

    /**
    * Sets the background color.
    *
    * @param BackgroundColor|string $color The background color.
    * @return self
    */
    public function bgColor(BackgroundColor|string $color): self
    {
        $this->bgColorRule = $color instanceof BackgroundColor
            ? $color->get()
            : Colors::hexBg($color)->get();

        return $this;
    }

    /**
    * Appends a repeated string to the current text.
    *
    * @param string $content The string to repeat.
    * @param int $repeat The number of times to repeat the string.
    * @return self
    */
    public function repeat(string $content, int $repeat = 1): self
    {
        $repeat = max($repeat, 0);

        return $this->push(
            str_repeat($content, $repeat)
        );
    }

    /**
    * Appends a specified number of newline characters.
    *
    * @deprecated Use {@see newLines()} instead.
    *
    * @param int $repeat The number of new lines to insert.
    * @return self
    */
    public function new_lines(int $repeat = 1): self
    {
        return $this->repeat("\n", $repeat);
    }

    /**
    * Appends a specified number of newline characters.
    *
    * @param int $repeat The number of new lines to insert.
    * @return self
    */
    public function newLines(int $repeat = 1): self
    {
        return $this->repeat("\n", $repeat);
    }

    /**
    * Appends a specified number of tab characters.
    *
    * @param int $count The number of tabs to insert.
    * @return self
    */
    public function tabs(int $count = 1): self
    {
        return $this->repeat("\t", $count);
    }
}
