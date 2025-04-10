<?php

declare(strict_types=1);

namespace System\Validator;

use Exception;
use System\Validator\Messages\Message;
use System\Validator\Messages\MessagePool;
use System\Validator\Rule\Rule;
use System\Validator\Rule\Filter;
use System\Validator\Rule\FilterPool;
use System\Validator\Rule\Valid;
use System\Validator\Rule\ValidPool;
use function call_user_func;
use function call_user_func_array;

/**
 * @property Collection $errors
 * @property Collection $filters
 */
class Validator
{
    private Rule $Rule;

    /** @var array<string, mixed> */
    private array $fields = [];

    /** @var ValidPool Valid rule collection */
    private ValidPool $validPool;

    /** @var FilterPool Filter rule collection */
    private FilterPool $filterPool;

    /** @var bool Check rule validate has run or not */
    private bool $hasRunValidate = false;

    /** @var MessagePool[] */
    private array $messages = [];

    /**
     * Create validation and filter.
     *
     * @param array<string, mixed> $fields Field array to validate
     * @return void
     */
    public function __construct(array $fields = [])
    {
        $this->Rule        = new Rule();
        $this->fields($fields);
        $this->validPool  = new ValidPool();
        $this->filterPool = new FilterPool();
    }

    /**
     * Create validation and filter using static.
     *
     * @param array<string, mixed>                           $fields       Field array to validate
     * @param callable(ValidPool=): (ValidPool|mixed)|null   $validatePool Closure with param as ValidPool
     * @param callable(FilterPool=): (FilterPool|mixed)|null $filterPool   Closure with param as ValidPool
     * @return static
     */
    public static function make(
        array    $fields = [],
        callable $validatePool = null,
        callable $filterPool = null
    ): self {
        $validate = new static($fields);
        if ($validatePool !== null) {
            $validate->validation($validatePool);
        }

        if ($filterPool !== null) {
            $validate->filters($filterPool);
        }

        return $validate;
    }

    /**
     * Set new field rule.
     *
     * @param string $name  Field name
     * @param string $value Validation Rule
     * @return void
     */
    public function __set(string $name, string $value): void
    {
        $this->field($name)->raw($value);
    }

    /**
     * Add new valid rule.
     *
     * @param string $name Field name
     * @return Valid|Collection<string, mixed> New rule Validation
     * @throws Exception
     */
    public function __get(string $name): Valid|Collection
    {
        if ($name === 'errors') {
            return $this->errors();
        }

        if ($name === 'filters') {
            return new Collection($this->filterOut());
        }

        return $this->field($name);
    }

    /**
     * Add new valid rule.
     *
     * @param string ...$field Field name
     * @return Valid New rule Validation
     */
    public function __invoke(string ...$field): Valid
    {
        return $this->field(...$field);
    }

    /**
     * Add new valid rule.
     *
     * @param string ...$field Field name
     * @return Valid New rule Validation
     */
    public function field(string ...$field): Valid
    {
        $this->hasRunValidate = false;

        return $this->validPool->rule(...$field);
    }

    /**
     * Add new filter rule.
     *
     * @param string ...$field Field name
     * @return Filter New rule filter
     */
    public function filter(string ...$field): Filter
    {
        return $this->filterPool->rule(...$field);
    }

    /**
     * Set fields or input for validation.
     *
     * @param array<string, mixed> $fields Field array to validate
     * @return self
     */
    public function fields(array $fields): self
    {
        foreach ($fields as $key => $field) {
            $this->fields[$key] = $field;
        }

        return $this;
    }

    /**
     * get fields or input validation.
     *
     * @return array<string, mixed> Fields
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Process the validation errors and return an array of errors with field names as keys.
     *
     * @return array<string, string> Validation errors
     * @throws Exception
     */
    public function getError(): array
    {
        if (!$this->hasRunValidate) {
            $this->Rule->validate($this->fields, $this->validPool->getPool());
            $this->hasRunValidate = true;
        }

        $this->setMessages();

        return $this->Rule->get_errors_array();
    }

    /**
     * Process the validation errors and return an array of errors with field names as keys.
     *
     * @return Collection<string, string> Validation errors
     * @throws Exception
     */
    public function errors(): Collection
    {
        return new Collection($this->getError());
    }

    /**
     * Inline validation field.
     *
     * @param callable(ValidPool=): (ValidPool|mixed)|null $ruleValidation Closure with param as ValidPool,
     *                                                                 if null return validate this current validation
     * @return bool
     * @throws Exception
     */
    public function isValid(callable $ruleValidation = null): bool
    {
        // load from property
        if ($ruleValidation === null) {
            $this->hasRunValidate = true;

            return $this->Rule->validate($this->fields, $this->validPool->getPool()) === true;
        }

        // load from param (convert to ValidPool)
        $rules = $this->closureToValidation($ruleValidation)->getPool();
        $this->Rule->validation_rules($rules);

        return $this->Rule->run($this->fields) !== false;
    }

    /**
     * Inline validation field.
     * Invert from is_valid.
     *
     * @param callable(ValidPool=): (ValidPool|mixed)|null $ruleValidation Closure with param as ValidPool,
     *                                                                 if null return validate this current validation
     * @return bool True if have a error
     * @throws Exception
     */
    public function is_error(callable $ruleValidation = null): bool
    {
        return !$this->isValid($ruleValidation);
    }

    /**
     * Execute closer when validation is true,
     * and return else statement.
     *
     * @param callable(ValidPool=): (ValidPool|mixed) $condition Execute closure
     * @return ValidationCondition
     * @throws Exception
     */
    public function ifValid(callable $condition): ValidationCondition
    {
        $val = $this->Rule->validate($this->fields, $this->validPool->getPool());

        if ($val === true) {
            call_user_func($condition);

            return new ValidationCondition([]);
        }

        return new ValidationCondition($val);
    }

    /**
     * Run validation, and throw error when false.
     *
     * @param Exception|null $exception Default throw exception
     * @return bool Return true if validation valid
     * @throws Exception
     */
    public function validOrException(?Exception $exception = null): bool
    {
        if ($this->Rule->validate($this->fields, $this->validPool->getPool()) === true) {
            return true;
        }

        throw $exception ?? new Exception('validate if fallen', 1);
    }

    /**
     * Run validation, and get error when false.
     *
     * @return bool|array<int, string> Return true if validation valid
     * @throws Exception
     */
    public function validOrError(?Exception $exception = null): array|bool
    {
        return $this->Rule->validate($this->fields, $this->validPool->getPool());
    }

    /**
     * Filter the input data.
     *
     * @param callable(FilterPool=): (FilterPool|mixed)|null $ruleFilter Closure of FilterPool
     * @return array<string, mixed> Fields input after filter
     * @throws Exception
     */
    public function filterOut(callable $ruleFilter = null): array
    {
        if ($ruleFilter === null) {
            /** @var array<string, mixed> $filter */
            $filter = (array) $this->Rule->filter($this->fields, $this->filterPool->getPool());

            return $filter;
        }

        // overwrite input field
        $rules_filter          = $this->fields;
        // replace input field with filter
        foreach ($this->closureToFilter($ruleFilter)->getPool() as $field => $rule) {
            $rules_filter[$field] = $rule->getFilter();
        }

        /** @var array<string, mixed> $filter */
        $filter = (array) $this->Rule->filter($this->fields, $rules_filter);

        return $filter;
    }

    /**
     * Run validation and filter if success.
     *
     * @return array|true True if validation failed,
     *                    array filter if validation valid
     * @throws Exception
     */
    public function failedOrFilter(): array|true
    {
        if ($this->Rule->validate($this->fields, $this->validPool->getPool()) === true) {
            return $this->filterOut();
        }

        return true;
    }

    /**
     * Change language for error messages.
     * Can effect before run validation or filter.
     *
     * @param string $lang Language
     * @return self
     */
    public function lang(string $lang): self
    {
        $this->Rule->lang($lang);

        return $this;
    }

    /**
     * Adding validation rule using ValidPool Callback.
     * Pass param as ValidPool in callback to adding rule.
     *
     * @param callable(ValidPool=): (ValidPool|mixed) $pools Closure with param as ValidPool
     * @return self
     */
    public function validation(callable $pools): self
    {
        $this->validPool->combine(
            $this->closureToValidation($pools)
        );

        return $this;
    }

    /**
     * Adding Filter rule using FilterPool Callback.
     * Pass param as FilterPool in callback to adding rule.
     *
     * @param callable(FilterPool=): (FilterPool|mixed) $pools Closure with param as FilterPool
     * @return self
     */
    public function filters(callable $pools): self
    {
        $this->filterPool->combine(
            $this->closureToFilter($pools)
        );

        return $this;
    }

    /**
     * Helper to get rules from Closure.
     *
     * @param callable(ValidPool=): (ValidPool|mixed) $ruleValidation closure of ValidPool
     * @return ValidPool Validation rules
     */
    private function closureToValidation(callable $ruleValidation): ValidPool
    {
        $pool  = new ValidPool();

        $returnClosure = call_user_func_array($ruleValidation, [$pool]);

        return $returnClosure instanceof ValidPool
            ? $returnClosure
            : $pool
        ;
    }

    /**
     * Helper to get rules from Closure.
     *
     * @param callable(FilterPool=): (FilterPool|mixed) $ruleFilter closure of FilterPoll
     * @return FilterPool Filter rules
     */
    private function closureToFilter(callable $ruleFilter): FilterPool
    {
        $pool  = new FilterPool();

        $returnClosure = call_user_func_array($ruleFilter, [$pool]);

        return $returnClosure instanceof FilterPool
            ? $returnClosure
            : $pool
        ;
    }

    /**
     * Helper to get custom message from Closure.
     *
     * @param callable(MessagePool=): (MessagePool|mixed) $ruleFilter closure of MessagePool
     * @return MessagePool Custom error Message
     */
    private function closureToMessages(callable $ruleFilter): MessagePool
    {
        $pool  = new MessagePool();

        $returnClosure = call_user_func_array($ruleFilter, [$pool]);

        return $returnClosure instanceof MessagePool
            ? $returnClosure
            : $pool
        ;
    }

    /**
     * Set field-rule specific error messages.
     *
     * @param callable(MessagePool=): (MessagePool|mixed)|null $pools Closure with param as MessagePool
     */
    public function messages(callable $pools = null): MessagePool
    {
        $pools ??= static fn () => new MessagePool();

        return $this->messages[] = $this->closureToMessages($pools);
    }

    /**
     * Set field-rule specific error messages.
     *
     * @param array<string, array<string, string>> $messages
     * @return void
     */
    public function setErrorMessages(array $messages): void
    {
        foreach ($messages as $field => $message_string) {
            $message_pool = new MessagePool();

            $this->messages[] = $message_pool->set($field, (new Message())->add($message_string));
        }
    }

    /**
     * Convert Messages class to array messages.
     *
     * @return void
     */
    private function setMessages(): void
    {
        $messages = [];
        foreach ($this->messages as $messagePool) {
            foreach ($messagePool->Messages() as $filed => $message) {
                $messages[$filed] = $message;
            }
        }

        $this->Rule->set_fields_error_messages($messages);
    }

    /**
     * Check validation has submitted form.
     *
     * @return bool True if from submitted form
     */
    public function submitted(): bool
    {
        return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Validation field and submitted check.
     *
     * @return bool True if pass is_valid and submitted
     * @throws Exception
     */
    public function passed(): bool
    {
        return $this->isValid() && $this->submitted();
    }

    /**
     * Validation field and submitted check.
     * Invert method passed().
     *
     * @return bool True if not pass is_valid and submitted
     * @throws Exception
     */
    public function fails(): bool
    {
        return !$this->passed();
    }

    /**
     * Filter validation only allow field.
     *
     * @param array<int, string> $fields Fields allow to validation
     * @return self
     */
    public function only(array $fields): self
    {
        $this->validPool->only($fields);

        return $this;
    }

    /**
     * Filter validation except some field.
     *
     * @param array<int, string> $fields Fields not allow to validation
     * @return self
     */
    public function except(array $fields): self
    {
        $this->validPool->except($fields);

        return $this;
    }
}
