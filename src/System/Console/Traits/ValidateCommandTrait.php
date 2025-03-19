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

use Exception;
use System\Console\Style\Alert;
use System\Console\Style\Style;
use System\Validator\Rule\ValidPool;
use System\Validator\Validator;

/**
 * The `ValidateCommandTrait` provides validation support for command-line inputs.
 *
 * This trait initializes a validation system using a `Validator` instance, applies validation
 * rules, checks validation status, and retrieves validation error messages for formatted output.
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
trait ValidateCommandTrait
{
    /** @var Validator Holds a Validator instance for input validation. */
    protected Validator $validate;

    /**
     * Initialize validation with provided inputs.
     *
     * This method creates a new `Validator` instance and applies validation rules.
     *
     * @param array<string, string|bool|int|null> $inputs The input data to validate.
     * @return void
     */
    protected function initValidate(array $inputs): void
    {
        $this->validate = new Validator($inputs);
        $this->validate->validation(
            fn (ValidPool $rules) => $this->validateRule($rules)
        );
    }

    /**
     * Define validation rules.
     *
     * This method should be overridden to specify validation rules for the input data.
     *
     * @param ValidPool $rules The validation rule set.
     * @return void
     */
    protected function validateRule(ValidPool $rules): void
    {
    }

    /**
     * Check if the input validation passed.
     *
     * @return bool Returns `true` if validation passes, otherwise `false`.
     */
    protected function isValid(): bool
    {
        return $this->validate->is_valid();
    }

    /**
     * Retrieve validation error messages and format them for output.
     *
     * If there are validation errors, they are formatted and appended to the `Style` instance.
     *
     * @param Style $style The style instance to append error messages to.
     * @return Style The updated `Style` instance.
     * @throws Exception If an error occurs during rendering.
     */
    protected function getValidateMessage(Style $style): Style
    {
        //foreach ($this->validate->get_error() as $input => $message) {
        foreach ($this->validate->get_error() as $message) {
            $style->tap(
                Alert::render()->warn($message)
            );
        }

        return $style;
    }
}
