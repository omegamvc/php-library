<?php

declare(strict_types=1);

namespace System\Text;

use System\Text\Exceptions\NoReturnException;

class Text
{
    /**
     * Orinal string input.
     *
     * @var string
     */
    private string $original;

    /**
     * Current string.
     *
     * @var string
     */
    private string $current;

    /**
     * Log string modifier.
     *
     * @var array<string, array<string, string>>
     */
    private array $latest;

    /**
     * Throw when string method return 'false' instance 'string'.
     *
     * @var bool
     */
    private bool $throwOnFailure = false;

    /**
     * Create string class.
     *
     * @param string $text Input string
     */
    public function __construct(string $text)
    {
        $this->original = $text;
        $this->execute($text, __FUNCTION__);
    }

    /**
     * Basicly is history for text modify.
     *
     * @param string|bool|array<int|string, string> $text          new incoming text
     * @param string                                $function_name Method to call (Str::class)
     *
     * @return string
     */
    private function execute($text, string $function_name)
    {
        if (Str::isString($text)) {
            $this->current = $text;
        }

        $this->latest[] = [
            'function'  => $function_name,
            'return'    => $text,
            'type'      => \gettype($text),
        ];

        return $text;
    }

    /**
     * Push new string text without erase history.
     *
     * @param string $text New text
     *
     * @return self
     */
    public function text(string $text)
    {
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Get last/current string text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->current;
    }

    /**
     * Get last/current string text.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getText();
    }

    /**
     * Get string history.
     *
     * @return array<string, array<string, string>>
     */
    public function logs()
    {
        return $this->latest;
    }

    /**
     * Reset or flush this class to origin string.
     *
     * @return self
     */
    public function reset()
    {
        $this->current          = $this->original;
        $this->latest           = [];
        $this->throwOnFailure = false;

        return $this;
    }

    /**
     * Refresh class with new text.
     *
     * @param string $text Input string
     *
     * @return self
     */
    public function refresh(string $text)
    {
        $this->original = $text;

        return $this->reset();
    }

    /**
     * Throw when string method return 'false' instance 'string'.
     *
     * @param bool $throw_error Throw on failure
     *
     * @return self
     */
    public function throwOnFailure(bool $throw_error)
    {
        $this->throwOnFailure = $throw_error;

        return $this;
    }

    // logic ----------------------------------

    /**
     * Return the character at the specifid postion.
     *
     * @param int $index character position
     *
     * @return self
     */
    public function chartAt(int $index)
    {
        $text = Str::chartAt($this->current, $index);

        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Extracts a section of string.
     *
     * @param int      $start  Start position text
     * @param int|null $length Length of string
     *
     * @return self
     */
    public function slice(int $start, ?int $length = null)
    {
        $text = Str::slice($this->current, $start, $length);

        if ($this->throwOnFailure && false === $text) {
            throw new NoReturnException(__FUNCTION__, $this->current);
        }

        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Convert string to lowercase.
     *
     * @return self
     */
    public function lower()
    {
        $text = Str::toLowerCase($this->current);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Convert string to lowercase.
     *
     * @return self
     */
    public function upper()
    {
        $text = Str::toUpperCase($this->current);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Make frist charater uppercase.
     *
     * @return self
     */
    public function firstUpper()
    {
        $text = Str::firstUpper($this->current);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Make frist charater uppercase each words.
     *
     * @return self
     */
    public function firstUpperAll()
    {
        $text = Str::firstUpperAll($this->current);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Make text sparate with dash (snackcase).
     *
     * @return self
     */
    public function snack()
    {
        $text = Str::toSnackCase($this->current);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Make text sparate with - (kebabcase).
     *
     * @return self
     */
    public function kebab()
    {
        $text = Str::toKebabCase($this->current);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Make text each word start with capital (pascalcase).
     *
     * @return self
     */
    public function pascal()
    {
        $text = Str::toPascalCase($this->current);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Make text camelcase.
     *
     * @return self
     */
    public function camel()
    {
        $text = Str::toCamelCase($this->current);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Make text each word start with capital (pascalcase).
     *
     * @return self
     */
    public function slug()
    {
        $text = Str::slug($this->current);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Fill string (start) with string if length is less.
     *
     * @param string $fill   String fill for miss length
     * @param int    $length Max length of output string
     *
     * @return self
     */
    public function fill(string $fill, $length)
    {
        $text = Str::fill($this->current, $fill, $length);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Fill string (end) with string if length is less.
     *
     * @param string $fill   String fill for miss length
     * @param int    $length Max length of output string
     *
     * @return self
     */
    public function fillEnd(string $fill, $length)
    {
        $text = Str::fillEnd($this->current, $fill, $length);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Create mask string.
     *
     * @param string $mask        Mask
     * @param int    $start       Start postion mask
     * @param int    $mask_length Mask lenght
     *
     * @return self
     */
    public function mask(string $mask, int $start, int $mask_length = 9999)
    {
        $text = Str::mask($this->current, $mask, $start, $mask_length);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Truncate text to limited length.
     *
     * @param int    $length            Length text
     * @param string $truncate_caracter Truncate caracter
     *
     * @return self
     */
    public function limit(int $length, string $truncate_caracter = '...')
    {
        $text = Str::limit($this->current, $length, $truncate_caracter);
        $this->execute($text, __FUNCTION__);

        return $this;
    }

    /**
     * Get text after text finded.
     */
    public function after(string $find): self
    {
        $this->execute(
            Str::after($this->current, $find),
            __FUNCTION__
        );

        return $this;
    }

    // int -----------------------------------------------

    /**
     * Get string length (0 if empty).
     *
     * @return int
     */
    public function length()
    {
        return Str::length($this->current);
    }

    /**
     * Index of first occorrent of specified text with in string.
     *
     * @param string $find Find
     *
     * @return int|false
     */
    public function indexOf(string $find)
    {
        return Str::indexOf($this->current, $find);
    }

    /**
     * Last index of first occorrent of specified text with in string.
     *
     * @param string $find Find
     *
     * @return int|false
     */
    public function lastIndexOf(string $find)
    {
        return Str::lastIndexOf($this->current, $find);
    }

    // boolean -------------------------------------------

    /**
     * Check string is empty string.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return Str::isEmpty($this->current);
    }

    /**
     * Check string is empty string.
     *
     * @param string $pattern String leguler expresstion
     *
     * @return bool
     */
    public function is(string $pattern)
    {
        return Str::isMatch($this->current, $pattern);
    }

    /**
     * Check string is empty string.
     *
     * @param string $pattern String leguler expresstion
     *
     * @return bool
     */
    public function isMatch(string $pattern)
    {
        return $this->is($pattern);
    }

    /**
     * Check text contain with.
     *
     * @param string $find Text contain
     *
     * @return bool True if text contain
     */
    public function contains(string $find)
    {
        return Str::contains($this->current, $find);
    }

    /**
     * Check text starts with with.
     *
     * @param string $start_with Start with
     *
     * @return bool True if text starts with
     */
    public function startsWith(string $start_with)
    {
        return Str::startsWith($this->current, $start_with);
    }

    /**
     * Check text ends with with.
     *
     * @param string $end_with Start with
     *
     * @return bool True if text ends with
     */
    public function endsWith(string $end_with)
    {
        return Str::endsWith($this->current, $end_with);
    }
}
