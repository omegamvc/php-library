<?php

declare(strict_types=1);

namespace System\Validator;

use GUMP;
use System\Validator\Traits\CostumeFilterTrait;
use System\Validator\Traits\CostumeValidationTrait;
use System\Validator\Traits\InvertValidationTrait;

class Rule extends GUMP
{
    // validation
    use InvertValidationTrait;
    use CostumeValidationTrait;
    // filter
    use CostumeFilterTrait;

    /**
     * Change language for error messages.
     * Can effect before run validation or filter.
     *
     * @param string $lang Language
     */
    public function lang(string $lang): self
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get all error messages.
     *
     * @return array<string, string>
     */
    protected function get_messages(): array
    {
        $messages = parent::get_messages();

        // add inverter costume validate message
        foreach ($messages as $rule => $message) {
            $rule_key = 'invert_' . $rule;
            if (!isset($messages[$rule_key])) {
                $messages[$rule_key] = $message;
            }
        }

        return $messages;
    }
}
