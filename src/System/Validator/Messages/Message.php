<?php

declare(strict_types=1);

namespace System\Validator\Messages;

use ArrayAccess;
use System\Validator\ValidationPropertyInterface;

/**
 * @implements ArrayAccess<string,string>
 */
class Message implements ArrayAccess, ValidationPropertyInterface
{
    /** @var array<string, string> */
    private array $messages = [];

    /**
     * Set message value using __set.
     *
     * @param string $rule
     * @param string $message
     * @return void
     */
    public function __set(string $rule, string $message)
    {
        $this->set($rule, $message);
    }

    /**
     * Add error message directive to collection.
     *
     * @param string $rule
     * @param string $message
     * @return self
     */
    public function set(string $rule, string $message): self
    {
        $this->messages[$rule] = $message;

        return $this;
    }

    /**
     * Add error message directive to collection.
     *
     * @param array<string, string> $errorMessages
     * @return self
     */
    public function add(array $errorMessages): self
    {
        foreach ($errorMessages as $rule => $message) {
            $this->set($rule, $message);
        }

        return $this;
    }

    /**
     * Get messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->messages;
    }

    /**
     * Assigns a value to the specified offset.
     *
     * @param string $offset the offset to assign the value to
     * @param string $value  the value to set
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->messages[$offset] = $value;
    }

    /**
     * Whether or not an offset exists.
     * This method is executed when using isset() or empty().
     *
     * @param string $offset an offset to check for
     * @return bool returns true on success or false on failure
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->messages[$offset]);
    }

    /**
     * Unsets an offset.
     *
     * @param string $offset unsets an offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->messages[$offset]);
    }

    /**
     * Returns the value at specified offset.
     *
     * @param string $offset the offset to retrieve
     *
     * @return string|null Can return all value types
     */
    public function offsetGet(mixed $offset): ?string
    {
        return $this->messages[$offset] ?? null;
    }
}
