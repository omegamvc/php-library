<?php

declare(strict_types=1);

namespace System\Validator\Rule;

use Exception;
use Random\RandomException;

use function bin2hex;
use function call_user_func_array;
use function implode;
use function is_bool;
use function random_bytes;

class Filter
{
    /** @var string[] */
    private array $filterRule = [];

    /** @var string */
    private string $delimiter = '|';

    public function __construct()
    {
        $this->delimiter = Rule::$rules_delimiter;
    }

    /**
     * Function to create and return previously created instance.
     */
    public static function with(): self
    {
        return new static();
    }

    /**
     * @return string Rule of Filter
     */
    public function getFilter(): string
    {
        $isBlockIf     = false;
        $filtersRule   = [];

        foreach ($this->filterRule as $rule) {
            // detect if condition
            if ($rule === 'if_false') {
                $isBlockIf = true;
                continue;
            }
            if ($rule === 'if_true' || $rule === 'end_if') {
                // break if statement
                $isBlockIf = false;
                continue;
            }
            // block rule if statement is false
            if ($isBlockIf === true) {
                continue;
            }

            // add string rule and reset invert rule
            $filtersRule[]    = $rule;
            $is_invert         = false;
        }

        return implode($this->delimiter, $filtersRule);
    }

    /**
     * Combine filter rule with other filter rule.
     *
     * @param Filter $filter Filter class to combine
     * @return self
     */
    public function combine(Filter $filter): self
    {
        foreach ($filter->filterRule as $rule) {
            $this->filterRule[] = $rule;
        }

        return $this;
    }

    /**
     * @return string Rule of filter
     */
    public function __toString(): string
    {
        return $this->getFilter();
    }

    /**
     * Rule will be apply if condition is true,
     * otherwise rule be reset (not set) if return false.
     *
     * Reset only boolean false.
     *
     * @param callable(): bool $condition Closure return boolean
     * @throws Exception
     */
    public function where(callable $condition): string
    {
        // get return closure
        $result = call_user_func_array($condition, []);
        // throw exception if closure not return boolean
        if (!is_bool($result)) {
            throw new Exception('Condition closure not return boolean', 1);
        }

        // false condition
        if ($result === false) {
            $this->filterRule = [];
        }

        // prevent create new rule and give a string rule
        return $this->getFilter();
    }

    /**
     * Rule will be apply if condition is true,
     * otherwise rule be skip if return false.
     *
     * Reset only boolean false.
     *
     * @param callable(): bool $condition Closure return boolean
     * @return self
     * @throws Exception
     */
    public function if(callable $condition): self
    {
        // get return closure
        $result = call_user_func_array($condition, []);
        // throw exception if closure not return boolean
        if (!is_bool($result)) {
            throw new Exception('Condition closure not return boolean', 1);
        }

        // add condition to rule
        $this->filterRule[] = $result
            ? 'if_true'
            : 'if_false'
        ;

        return $this;
    }

    /**
     * Set end rule of 'if' statement.
     *
     * @return self
     */
    public function endIf(): self
    {
        $this->filterRule[] = 'end_if';

        return $this;
    }

    /**
     * Adding custom Filter.
     *
     * @template T
     *
     * @param callable(T, array<string, string>): T $customFilter Callable return as string,
     *                                                              can contain param as ($value, $params)
     *
     * @return self
     * @throws Exception
     * @throws RandomException
     */
    public function filter(callable $customFilter): self
    {
        if (is_callable($customFilter)) {
            $byte     = random_bytes(3);
            $hex      = bin2hex($byte);
            $ruleName = 'filter_' . $hex;

            Rule::add_filter($ruleName, $customFilter);

            $this->filterRule[] = $ruleName;
        }

        return $this;
    }

    /**
     * Add filter rule with raw (string) rule.
     *
     * @param string $rawRule Raw rule
     * @return self
     */
    public function raw(string $rawRule): self
    {
        $this->filterRule[] = $rawRule;

        return $this;
    }

    /**
     * Replace noise words in a string.
     *
     * @return self
     */
    public function noiseWords(): self
    {
        $this->filterRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Remove all known punctuation from a string.
     *
     * @return self
     */
    public function rmPunctuation(): self
    {
        $this->filterRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Sanitize the string by urlencoding characters.
     *
     * @return self
     */
    public function urlEncode(): self
    {
        $this->filterRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Sanitize the string by converting HTML characters to their HTML entities.
     *
     * @return self
     */
    public function htmlEncode(): self
    {
        $this->filterRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Sanitize the string by removing illegal characters from emails.
     *
     * @return self
     */
    public function sanitizeEmail(): self
    {
        $this->filterRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Sanitize the string by removing illegal characters from numbers.
     *
     * @return self
     */
    public function sanitizeNumbers(): self
    {
        $this->filterRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Sanitize the string by removing illegal characters from float numbers.
     *
     * @return self
     */
    public function sanitizeFloats(): self
    {
        $this->filterRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Sanitize the string by removing any script tags.
     *
     * @return self
     */
    public function sanitizeString(): self
    {
        $this->filterRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Converts ['1', 1, 'true', true, 'yes', 'on'] to true,
     * anything else is false ('on' is useful for form checkboxes).
     *
     * @return self
     */
    public function boolean(): self
    {
        $this->filterRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Filter out all HTML tags except the defined basic tags.
     *
     * @return self
     */
    public function basicTags(): self
    {
        $this->filterRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Convert the provided numeric value to a whole number.
     *
     * @return self
     */
    public function wholeNumber(): self
    {
        $this->filterRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Convert MS Word special characters to web safe characters.
     *
     * @return self
     */
    public function msWordCharacters(): self
    {
        $this->filterRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Converts to lowercase.
     *
     * @return self
     */
    public function lowerCase(): self
    {
        $this->filterRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Converts to uppercase.
     *
     * @return self
     */
    public function upperCase(): self
    {
        $this->filterRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Converts value to url-web-slugs.
     *
     * @return self
     */
    public function slug(): self
    {
        $this->filterRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Remove spaces from the beginning and end of strings (PHP).
     *
     * @return self
     */
    public function trim(): self
    {
        $this->filterRule[] = __FUNCTION__;

        return $this;
    }
}
