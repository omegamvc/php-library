<?php

declare(strict_types=1);

namespace System\Validator\Rule;


use Exception;
use Random\RandomException;

use function bin2hex;
use function call_user_func;
use function call_user_func_array;
use function implode;
use function is_bool;
use function is_callable;
use function random_bytes;

/**
 * @property self $not
 */
class Valid
{
    /** @var string[] */
    private array $validationRule = [];

    /** @var string */
    private string $delimiter;

    /** @var string */
    private string $parametersDelimiter;

    /** @var string */
    private string $parametersArraysDelimiter;

    public function __construct()
    {
        $this->delimiter                 = Rule::$rules_delimiter;
        $this->parametersDelimiter       = Rule::$rules_parameters_delimiter;
        $this->parametersArraysDelimiter = Rule::$rules_parameters_arrays_delimiter;
    }

    /**
     * Function to create and return previously created instance.
     */
    public static function with(): self
    {
        return new static();
    }

    /**
     * @return string Rule of validation
     */
    public function getValidation(): string
    {
        $isInvert       = false;
        $isBlockIf      = false;
        $validationRule = [];

        foreach ($this->validationRule as $rule) {
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

            // set next rule as invert rule
            if ($rule === 'invert') {
                $isInvert = !$isInvert;
                continue;
            }

            // add string rule and reset invert rule
            $validationRule[] = $isInvert ? 'invert_' . $rule : $rule;
            $isInvert         = false;
        }

        return implode($this->delimiter, $validationRule);
    }

    /**
     * Combine validation rule with other validation rule.
     *
     * @param Valid $valid Validation class to combine
     */
    public function combine(Valid $valid): self
    {
        foreach ($valid->validationRule as $rule) {
            $this->validationRule[] = $rule;
        }

        return $this;
    }

    /**
     * @return string Rule of validation
     */
    public function __toString(): string
    {
        return $this->getValidation();
    }

    /**
     * Access method from property.
     *
     * @param string $name Name of property or method
     * @return self
     */
    public function __get(string $name): self
    {
        if ($name === 'not') {
            return $this->not();
        }

        return $this;
    }

    /**
     * Call function may have alias.
     *
     * @param string $name      Methods name
     * @param array<int, string> $arguments Params method
     * @return self
     */
    public function __call(string $name, array $arguments): self
    {
        // backwards compatible until ver 1.x.x
        if ($name === 'equals_field') {
            $this->equalsfield($arguments[0]);
        }

        return $this;
    }

    /**
     * Set validation to invert result.
     *
     * @return self
     */
    public function not(): self
    {
        $this->validationRule[] = 'invert';

        return $this;
    }

    /**
     * Rule will be apply if condition is true,
     * otherwise rule be reset (not set) if return false.
     *
     * Reset only boolean false.
     *
     * @param callable(): bool $condition Closure return boolean
     * @return string
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
            $this->validationRule = [];
        }

        // prevent create new rule and give a string rule
        return $this->getValidation();
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
        $this->validationRule[] = $result
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
        $this->validationRule[] = 'end_if';

        return $this;
    }

    /**
     * Adding custom validation.
     *
     * @param callable(string, array<string, string>, array<string, string>, mixed): bool $customValidation
     * Callable return as boolean, can contain param as ($field, $input, $param, $value)
     * @param string $message Add custom message for validate
     * @return self
     * @throws Exception
     * @throws RandomException
     */
    public function valid(callable $customValidation, string $message = 'Valid custom validation'): self
    {
        if (is_callable($customValidation)) {
            $byte          = random_bytes(3);
            $hex           = bin2hex($byte);
            $ruleName      = 'validate_' . $hex;
            $ruleInvert    = 'invert_validate_' . $hex;
            $messageInvert = 'Not, ' . $message;
            $invert        = fn ($field, $input, $param, $value) => !call_user_func($customValidation, $field, $input, $param, $value);

            Rule::add_validator($ruleName, $customValidation, $message);
            Rule::add_validator($ruleInvert, $invert, $messageInvert);

            $this->validationRule[] = $ruleName;
        }

        return $this;
    }

    /**
     * Add validation rule with raw (string) rule.
     *
     * @param string $rawRule Raw rule
     * @return self
     */
    public function raw(string $rawRule): self
    {
        $this->validationRule[] = $rawRule;

        return $this;
    }

    /**
     * Ensures the specified key value exists and is not empty
     * (not null, not empty string, not empty array).
     *
     * @return self
     */
    public function required(): self
    {
        $this->validationRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Verify that a value is contained within the pre-defined value set.
     *
     * @param string ...$contain
     * @return $this
     */
    public function contains(string ...$contain): self
    {
        $strContains            = implode($this->parametersArraysDelimiter, $contain);
        $delimiter              = $this->parametersDelimiter;
        $this->validationRule[] = __FUNCTION__ . "$delimiter$strContains";

        return $this;
    }

    /**
     * Verify that a value is contained within the pre-defined value set.
     * Error message will NOT show the list of possible values.
     *
     * @param string ...$contain Contain
     * @return self
     */
    public function containsList(...$contain): self
    {
        $strContains            = implode($this->parametersArraysDelimiter, $contain);
        $delimiter              = $this->parametersDelimiter;
        $this->validationRule[] = __FUNCTION__ . "$delimiter$strContains";

        return $this;
    }

    /**
     * Verify that a value is contained within the pre-defined value set.
     * Error message will NOT show the list of possible values.
     *
     * @param string ...$contain Contain
     * @return self
     */
    public function doesntContainList(...$contain): self
    {
        $strContains            = implode($this->parametersArraysDelimiter, $contain);
        $delimiter              = $this->parametersDelimiter;
        $this->validationRule[] = __FUNCTION__ . "$delimiter$strContains";

        return $this;
    }

    /**
     * Determine if the provided value is a valid boolean.
     * Returns true for: yes/no, on/off, 1/0, true/false.
     * In strict mode (optional) only true/false will be valid which you can combine with boolean filter.
     *
     * @param bool $strict only true/false will be valid which you can combine with boolean filter
     * @return self
     */
    public function boolean(bool $strict = true): self
    {
        $useStrict              = $strict ? $this->parametersDelimiter . 'strict' : '';
        $this->validationRule[] = __FUNCTION__ . "$useStrict";

        return $this;
    }

    /**
     * Determine if the provided email has valid format.
     *
     * @return self
     */
    public function validEmail(): self
    {
        $this->validationRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Determine if the provided value length is less or equal to a specific value.
     *
     * @param int $len
     * @return self
     */
    public function maxLen(int $len): self
    {
        $this->validationRule[] = __FUNCTION__ . $this->parametersDelimiter . $len;

        return $this;
    }

    /**
     * Determine if the provided value length is more or equal to a specific value.
     *
     * @param int $len
     * @return self
     */
    public function minLen(int $len): self
    {
        $this->validationRule[] = __FUNCTION__ . $this->parametersDelimiter . $len;

        return $this;
    }

    /**
     * Determine if the provided value length matches a specific value.
     *
     * @param int $len Exact length
     * @return self
     */
    public function exactLen(int $len): self
    {
        $this->validationRule[] = __FUNCTION__ . $this->parametersDelimiter . $len;

        return $this;
    }

    /**
     * Determine if the provided value length is between min and max values.
     *
     * @param int $minLen Min length
     * @param int $maxLen Max length
     */
    public function betweenLen(int $minLen, int $maxLen): self
    {
        $this->validationRule[] = __FUNCTION__ . $this->parametersDelimiter . $minLen . $this->parametersArraysDelimiter . $maxLen;

        return $this;
    }

    /**
     * Determine if the provided value contains only alpha characters.
     *
     * @return self
     */
    public function alpha(): self
    {
        $this->validationRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Determine if the provided value contains only alpha-numeric characters.
     *
     * @return self
     */
    public function alphaNumeric(): self
    {
        $this->validationRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Determine if the provided value contains only alpha characters with dashed and underscores.
     *
     * @return self
     */
    public function alphaDash(): self
    {
        $this->validationRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Determine if the provided value contains only alpha numeric characters with dashed and underscores.
     *
     * @return self
     */
    public function alphaNumericDash(): self
    {
        $this->validationRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Determine if the provided value contains only alpha numeric characters with spaces.
     *
     * @return self
     */
    public function alphaNumericSpace(): self
    {
        $this->validationRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Determine if the provided value contains only alpha characters with spaces.
     *
     * @retun self
     */
    public function alphaSpace(): self
    {
        $this->validationRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Determine if the provided value is a valid number or numeric string.
     *
     * @return self
     */
    public function numeric(): self
    {
        $this->validationRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Determine if the provided value is a valid integer.
     *
     *
     * @return self
     */
    public function integer(): self
    {
        $this->validationRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Determine if the provided value is a valid float.
     *
     * @return self
     */
    public function float(): self
    {
        $this->validationRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Determine if the provided value is a valid URL.
     *
     * @return self
     */
    public function validUrl(): self
    {
        $this->validationRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Determine if a URL exists & is accessible.
     *
     * @return self
     */
    public function urlExists(): self
    {
        $this->validationRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Determine if the provided value is a valid IP address.
     *
     * @return self
     */
    public function validIp(): self
    {
        $this->validationRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Determine if the provided value is a valid IPv4 address.
     *
     * @return self
     */
    public function validIpv4(): self
    {
        $this->validationRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Determine if the provided value is a valid IPv6 address.
     *
     * @return self
     */
    public function validIpv6(): self
    {
        $this->validationRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Determine if the input is a valid credit card number.
     *
     * @return self
     */
    public function validCc(): self
    {
        $this->validationRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Determine if the input is a valid human name.
     *
     * @return self
     */
    public function validName(): self
    {
        $this->validationRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Determine if the provided input is likely to be a street address using weak detection.
     *
     * @return self
     */
    public function streetAddress(): self
    {
        $this->validationRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Determine if the provided value is a valid IBAN.
     *
     * @return self
     */
    public function iban(): self
    {
        $this->validationRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Determine if the provided input is a valid date (ISO 8601) or specify a custom format (optional).
     *
     * @param string $validDate String date with format d/m/Y
     * @return self
     *
     */
    public function date(string $validDate): self
    {
        $this->validationRule[] = __FUNCTION__ . $this->parametersDelimiter . $validDate;

        return $this;
    }

    /**
     * Determine if the provided input meets age requirement (ISO 8601).
     * Input should be a date (Y-m-d).
     *
     * @param int $age Age in integer
     * @return self
     */
    public function minAge(int $age): self
    {
        $this->validationRule[] = __FUNCTION__ . $this->parametersDelimiter . $age;

        return $this;
    }

    /**
     * Determine if the provided numeric value is lower or equal to a specific value.
     *
     * @param int $num Maximum Number
     * @return self
     */
    public function maxNumeric(int $num): self
    {
        $this->validationRule[] = __FUNCTION__ . $this->parametersDelimiter . $num;

        return $this;
    }

    /**
     * Determine if the provided numeric value is higher or equal to a specific value.
     *
     * @param int $num Minimum Number
     * @return self
     */
    public function minNumeric(int $num): self
    {
        $this->validationRule[] = __FUNCTION__ . $this->parametersDelimiter . $num;

        return $this;
    }

    /**
     * Determine if the provided value starts with param.
     *
     * @param string $startWith Starts with
     * @return self
     */
    public function starts(string $startWith): self
    {
        $this->validationRule[] = __FUNCTION__ . $this->parametersDelimiter . $startWith;

        return $this;
    }

    /**
     * Determine if the file was successfully uploaded.
     *
     * @return self
     */
    public function requiredFile(): self
    {
        $this->validationRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Check the uploaded file for extension.
     * Doesn't check mime-type yet.
     *
     * @param string ...$extension Extension without dot
     * @return self
     */
    public function extension(string ...$extension): self
    {
        $strContains            = implode($this->parametersArraysDelimiter, $extension);
        $delimiter              = $this->parametersDelimiter;
        $this->validationRule[] = __FUNCTION__ . "$delimiter$strContains";

        return $this;
    }

    /**
     * Determine if the provided field value equals current field value.
     *
     * @param string $fieldName Field value equals with
     */
    public function equalsfield(string $fieldName): self
    {
        $this->validationRule[] = __FUNCTION__ . $this->parametersDelimiter . $fieldName;

        return $this;
    }

    /**
     * Determine if the provided field value is a valid GUID (v4).
     *
     * @return self
     */
    public function guidv4(): self
    {
        $this->validationRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Determine if the provided value is a valid phone number.
     *
     * @return self
     */
    public function phoneNumber(): self
    {
        $this->validationRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Custom regex validator.
     *
     * @param string $regex Custom regex
     * @return self
     */
    public function regex(string $regex): self
    {
        $this->validationRule[] = __FUNCTION__ . $this->parametersDelimiter . $regex;

        return $this;
    }

    /**
     * Determine if the provided value is a valid JSON string.
     *
     * @return self
     */
    public function validJsonString(): self
    {
        $this->validationRule[] = __FUNCTION__;

        return $this;
    }

    /**
     * Check if an input is an array and if the size is more or equal to a specific value.
     *
     * @param int $arraySize Array dept size
     * @return self
     */
    public function validArraySizeGreater(int $arraySize): self
    {
        $this->validationRule[] = __FUNCTION__ . $this->parametersDelimiter . $arraySize;

        return $this;
    }

    /**
     * Check if an input is an array and if the size is less or equal to a specific value.
     *
     * @param int $arraySize Array dept size
     * @return self
     */
    public function validArraySizeLesser(int $arraySize): self
    {
        $this->validationRule[] = __FUNCTION__ . $this->parametersDelimiter . $arraySize;

        return $this;
    }

    /**
     * Check if an input is an array and if the size is equal to a specific value.
     *
     * @param int $arraySize Array dept size
     * @return self
     */
    public function validArraySizeEqual(int $arraySize): self
    {
        $this->validationRule[] = __FUNCTION__ . $this->parametersDelimiter . $arraySize;

        return $this;
    }
}
