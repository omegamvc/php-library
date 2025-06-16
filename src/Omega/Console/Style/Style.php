<?php

declare(strict_types=1);

namespace Omega\Console\Style;

use Closure;
use Omega\Console\Output\OutputStream;
use Omega\Console\Style\Color\RuleInterface;
use Omega\Console\Style\Color\BackgroundColor;
use Omega\Console\Style\Color\ForegroundColor;
use Omega\Console\Traits\CommandTrait;
use Omega\Text\Str;

use function method_exists;
use function Omega\Text\text;
use function preg_replace;
use function str_repeat;
use function strlen;
use function strtolower;

/**
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
 * @method text_red_500()
 * @method bg_blue_500()
 * @method text_red_10()
 */
class Style
{
    use CommandTrait;

    /**
     * Array of command rule.
     *
     * @var array<int, array<int, string|int>>
     */
    private array $rawRules = [];

    /**
     * Array of command rule.
     *
     * @var array<int, int>
     */
    private array $resetRules = [Decorate::RESET];

    /**
     * Rule of text color.
     *
     * @var array<int, int>
     */
    private array $textColorRule = [Decorate::TEXT_DEFAULT];

    /**
     * Rule of background color.
     *
     * @var array<int, int>
     */
    private array $bgColorRule = [Decorate::BG_DEFAULT];

    /**
     * Rule of text decorate.
     *
     * @var array<int, int>
     */
    private array $decorateRules = [];

    /**
     * String to style.
     *
     * @var string
     */
    private string $text;

    /**
     * Length of text.
     *
     * @var int
     */
    private int $length;

    /**
     * Reference from preview text (like prefix).
     *
     * @var string
     */
    private string $ref = '';

    private ?OutputStream $outputStream = null;

    /**
     * @param string $text set text to decorate
     */
    public function __construct(string $text = '')
    {
        $this->text   = $text;
        $this->length = strlen($text);
    }

    /**
     * Invoke new Rule class.
     *
     * @param string $text set text to decorate
     * @return self
     */
    public function __invoke(string $text): self
    {
        $this->text   = $text;
        $this->length = strlen($text);

        return $this->flush();
    }

    /**
     * Get string of terminal formatted style.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString($this->text, $this->ref);
    }

    /**
     * Call exist method from trait.
     *
     * @param string            $name
     * @param array<int, mixed> $arguments
     * @return self
     */
    public function __call(string $name, array $arguments): self
    {
        if (method_exists($this, $name)) {
            $constant = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name));

            if (Str::startsWith($name, 'text')) {
                $constant              = 'TEXT' . text($constant)->upper()->slice(4);
                $this->textColorRule = [Decorate::getConstant($constant)];
            }

            if (Str::startsWith($name, 'bg')) {
                $constant            =  'BG' . text($constant)->upper()->slice(2);
                $this->bgColorRule = [Decorate::getConstant($constant)];
            }

            return $this;
        }

        $constant = text($name)->upper();
        if ($constant->startsWith('TEXT_')) {
            $constant->slice(5);
            $this->textColor(Colors::hexText(ColorVariant::getConstant($constant->__toString())));
        }

        if ($constant->startsWith('BG_')) {
            $constant->slice(3);
            $this->bgColor(Colors::hexBg(ColorVariant::getConstant($constant->__toString())));
        }

        return $this;
    }

    /**
     * Render text, reference with current rule.
     *
     * @param string $text Text tobe render with rule (this)
     * @param string $ref  Text reference to be add begin text
     * @return string
     */
    public function toString(string $text, string $ref = ''): string
    {
        if ($text === '' && $ref === '') {
            return '';
        }

        $rules = [];

        foreach ($this->textColorRule as $textColor) {
            $rules[] = $textColor;
        }

        foreach ($this->bgColorRule as $bgColor) {
            $rules[] = $bgColor;
        }

        foreach ($this->decorateRules as $decorate) {
            $rules[] = $decorate;
        }

        foreach ($this->rawRules as $raws) {
            foreach ($raws as $raw) {
                $rules[] = $raw;
            }
        }

        return $ref . $this->rules($rules, $text, true, $this->resetRules);
    }

    /**
     * Flush class.
     *
     * @return self
     */
    public function flush(): self
    {
        $this->textColorRule = [Decorate::TEXT_DEFAULT];
        $this->bgColorRule   = [Decorate::BG_DEFAULT];
        $this->decorateRules = [];
        $this->resetRules    = [Decorate::RESET];
        $this->rawRules      = [];
        $this->ref           = '';

        return $this;
    }

    /**
     * Set reference (add before main text).
     *
     * @param string $textReference
     * @return self
     */
    private function ref(string $textReference): self
    {
        $this->ref = $textReference;

        return $this;
    }

    /**
     * Chain code (continue with other text).
     *
     * @param string $text text
     * @return self
     */
    public function push(string $text): self
    {
        $ref        = $this->toString($this->text, $this->ref);
        $this->text = $text;
        $this->length += strlen($text);

        return $this->flush()->ref($ref);
    }

    /**
     * Push Style.
     *
     * @param Style $style Style to push
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
     * Get text length without rule counted.
     *
     * @return int
     */
    public function length(): int
    {
        return $this->length;
    }

    /**
     * Print terminal style.
     *
     * @param bool $newLine True if print with new line in end line
     * @return void
     */
    public function out(bool $newLine = true): void
    {
        $out = $this . ($newLine ? PHP_EOL : null);

        echo $out;
    }

    /**
     * Print terminal style if condition true.
     *
     * @param Closure|bool $condition If true will echo out
     * @param bool $newLine  True if print with new line in end line
     * @return void
     */
    public function outIf(Closure|bool $condition, bool $newLine = true): void
    {
        if ($condition) {
            $out = $this . ($newLine ? PHP_EOL : null);

            echo $out;
        }
    }

    /**
     * Print to terminal and continue.
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
     * Write stream out.
     *
     * @param bool $newLine True if print with new line in end line
     * @return void
     */
    public function write(bool $newLine = true): void
    {
        $out = $this . ($newLine ? PHP_EOL : null);

        $this->outputStream?->write($out);
    }

    /**
     * Clear current line (original text is keep).
     *
     * @param int $line
     * @return void
     */
    public function clear(int $line = -1): void
    {
        $this->clearLine($line);
    }

    /**
     * Replace current line (original text is keep).
     *
     * @param string $text
     * @param int    $line
     * @return void
     */
    public function replace(string $text, int $line = -1): void
    {
        $this->replaceLine($text, $line);
    }

    /**
     * @param OutputStream $outputStream
     * @return $this
     */
    public function setOutputStream(OutputStream $outputStream): self
    {
        $this->outputStream = $outputStream;

        return $this;
    }

    /**
     * Reset all attributes (set reset decorate to be 0).
     *
     * @return self
     */
    public function resetDecorate(): self
    {
        $this->resetRules = [Decorate::RESET];

        return $this;
    }

    /**
     * Text decorate bold.
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
     * Text decorate underline.
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
     * Text decorate blink.
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
     * Text decorate reverse/invert.
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
     * Text decorate hidden.
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
     * Add raw terminal code.
     *
     * @param RuleInterface|string $raw Raw terminal code.
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
     * @param int[] $reset rules reset
     * @return self
     */
    public function rawReset(array $reset): self
    {
        $this->resetRules = $reset;

        return $this;
    }

    /**
     * Set text color.
     *
     * @param ForegroundColor|string $color
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
     * Set Background color.
     *
     * @param BackgroundColor|string $color
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
     * Push/insert repeat character.
     *
     * @param string $content
     * @param int    $repeat
     * @return self
     * @noinspection PhpConditionCanBeReplacedWithMinMaxCallInspection
     */
    public function repeat(string $content, int $repeat = 1): self
    {
        $repeat = $repeat < 0 ? 0 : $repeat;

        return $this->push(
            str_repeat($content, $repeat)
        );
    }

    /**
     * Push/insert new lines.
     *
     * @param int $repeat
     * @return self
     */
    public function newLines(int $repeat = 1): self
    {
        return $this->repeat("\n", $repeat);
    }

    /**
     * Push/insert tabs.
     *
     * @param int $count
     * @return self
     */
    public function tabs(int $count = 1): self
    {
        return $this->repeat("\t", $count);
    }
}