<?php

declare(strict_types=1);

namespace System\Validator\Rule;

use GUMP;
use System\Validator\Traits\CustomFilterTrait;
use System\Validator\Traits\CustomValidationTrait;
use System\Validator\Traits\InvertValidationTrait;

/**
 * @internal
 */
class Rule extends GUMP
{
    use InvertValidationTrait;
    use CustomValidationTrait;
    use CustomFilterTrait;

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

        // add invert custom validate message
        foreach ($messages as $rule => $message) {
            $ruleKey = 'invert_' . $rule;
            if (!isset($messages[$ruleKey])) {
                $messages[$ruleKey] = $message;
            }
        }

        return $messages;
    }
}
