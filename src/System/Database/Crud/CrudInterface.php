<?php

namespace System\Database\Crud;

use System\Collection\Collection;

interface CrudInterface
{
    /**
     * @return string|int
     */
    public function getID();

    /**
     * @param string|int $val
     *
     * @return static
     */
    public function setID($val);

    /**
     * Featch from database using primery_key and identifer.
     */
    public function read(): bool;

    public function cread(): bool;

    public function update(): bool;

    public function delete(): bool;

    public function isExist(): bool;

    public function getLastInsertID(): string;

    /**
     * Convert array to class property.
     *
     * @param array<string, mixed> $arr_column
     *
     * @return \System\Database\Crud\AbstractCrud
     */
    public function convertFromArray(array $arr_column);

    /**
     * Convert class property to array.
     *
     * @return array<string, mixed>
     */
    public function convertToArray(): array;

    /**
     * @return Collection<string, mixed>
     */
    public function toCollection(): Collection;

    public function isClean(?string $column = null): bool;

    public function isDirty(?string $column = null): bool;
}
