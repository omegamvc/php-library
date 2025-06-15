<?php

declare(strict_types=1);

namespace Omega\Database\MySchema\Table\Attributes\Alter;

class DataType
{
    /** @var string */
    private $name;
    /** @var string|Constraint */
    private $datatype;

    public function __construct(string $column_name)
    {
        $this->name     = $column_name;
        $this->datatype = '';
    }

    public function __toString()
    {
        return $this->query();
    }

    private function query(): string
    {
        return $this->name . ' ' . $this->datatype;
    }

    // number

    public function int(int $length = 0): Constraint
    {
        if ($length === 0) {
            return $this->datatype = new Constraint('int');
        }

        return $this->datatype = new Constraint("int($length)");
    }

    public function tinyint(int $length = 0): Constraint
    {
        if ($length === 0) {
            return $this->datatype = new Constraint('tinyint');
        }

        return $this->datatype = new Constraint("tinyint($length)");
    }

    public function smallint(int $length = 0): Constraint
    {
        if ($length === 0) {
            return $this->datatype = new Constraint('smallint');
        }

        return $this->datatype = new Constraint("smallint($length)");
    }

    public function bigint(int $length = 0): Constraint
    {
        if ($length === 0) {
            return $this->datatype = new Constraint('bigint');
        }

        return $this->datatype = new Constraint("bigint($length)");
    }

    public function float(int $length = 0): Constraint
    {
        if ($length === 0) {
            return $this->datatype = new Constraint('float');
        }

        return $this->datatype = new Constraint("float($length)");
    }

    // date

    public function time(int $length = 0): Constraint
    {
        if ($length === 0) {
            return $this->datatype = new Constraint('time');
        }

        return $this->datatype = new Constraint("time($length)");
    }

    public function timestamp(int $length = 0): Constraint
    {
        if ($length === 0) {
            return $this->datatype = new Constraint('timestamp');
        }

        return $this->datatype = new Constraint("timestamp($length)");
    }

    public function date(): Constraint
    {
        return $this->datatype = new Constraint('date');
    }

    // text

    public function varchar(int $length = 0): Constraint
    {
        if ($length === 0) {
            return $this->datatype = new Constraint('varchar');
        }

        return $this->datatype = new Constraint("varchar($length)");
    }

    public function text(int $length = 0): Constraint
    {
        if ($length === 0) {
            return $this->datatype = new Constraint('text');
        }

        return $this->datatype = new Constraint("text($length)");
    }

    public function blob(int $length = 0): Constraint
    {
        if ($length === 0) {
            return $this->datatype = new Constraint('blob');
        }

        return $this->datatype = new Constraint("blob($length)");
    }

    /**
     * @param string[] $enums
     */
    public function enum(array $enums): Constraint
    {
        $enums = array_map(fn ($item) => "'{$item}'", $enums);
        $enum  = implode(', ', $enums);

        return $this->datatype = new Constraint("ENUM ({$enum})");
    }

    public function after(string $column): void
    {
        $this->datatype = "AFTER {$column}";
    }

    public function first(): void
    {
        $this->datatype = 'FIRST';
    }
}
