<?php

declare(strict_types=1);

namespace System\Template;

use System\Template\Traits\CommentTrait;
use System\Template\Traits\FormatterTrait;

class Method
{
    use FormatterTrait;
    use CommentTrait;

    public const int PUBLIC_    = 0;
    public const int PRIVATE_   = 1;
    public const int PROTECTED_ = 2;

    private int $visibility = -1;
    private bool $isFinal   = false;
    private bool $isStatic  = false;

    private string $name;

    /** @var string[] */
    private array $params              = [];

    private ?string $returnType = null;

    /** @var string[] */
    private array $body = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->generate();
    }

    public static function new(string $name): self
    {
        return new self($name);
    }

    public function planTemplate(): string
    {
        return $this->customize_template
            ?? "{{comment}}{{before}}function {{name}}({{params}}){{return type}}{{new line}}{\n{{body}}{{new line}}}";
    }

    public function generate(): string
    {
        $tempalate = $this->planTemplate();
        $tab_dept  = fn (int $dept) => str_repeat($this->tab_indent, $dept * $this->tab_size);
        // new line
        $new_line = "\n" . $tab_dept(1);

        // comment
        $comment = $this->generateComment(1, $this->tab_indent);
        $comment = count($this->comments) > 0
            ? $comment . $new_line
            : $comment;

        $pre = [];
        // final
        $pre[] = $this->isFinal ? 'final' : '';

        // static
        $pre[] = $this->isStatic ? 'static' : '';

        // visibility
        $pre[] = match ($this->visibility) {
            self::PUBLIC_    => 'public',
            self::PRIVATE_   => 'private',
            self::PROTECTED_ => 'protected',
            default          => '',
        };

        // {{final}}{{visibility}}{{static}}
        $pre    = array_filter($pre);
        $before = implode(' ', $pre);
        $before .= count($pre) == 0 ? '' : ' ';

        // name
        $name = $this->name;

        // params
        $params = implode(', ', $this->params);

        // return type
        $return = isset($this->returnType) ? ': ' : '';
        $return .= $this->returnType;

        // body
        $bodys = array_map(fn ($x) => $tab_dept(2) . $x, $this->body);
        $body  = implode("\n", $bodys);

        return str_replace(
            ['{{comment}}', '{{before}}', '{{name}}', '{{params}}', '{{new line}}', '{{body}}', '{{return type}}'],
            [$comment, $before, $name, $params, $new_line, $body, $return],
            $tempalate
        );
    }

    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function visibility(int $visibility = self::PUBLIC_): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function isFinal(bool $is_final = true): self
    {
        $this->isFinal = $is_final;

        return $this;
    }

    public function isStatic(bool $is_static = true): self
    {
        $this->isStatic = $is_static;

        return $this;
    }

    /**
     * @param string[]|null $params
     */
    public function params(?array $params): self
    {
        $this->params = $params ?? [];

        return $this;
    }

    public function addParams(string $param): self
    {
        $this->params[] = $param;

        return $this;
    }

    public function setReturnType(?string $returnType): self
    {
        $this->returnType = $returnType ?? '';

        return $this;
    }

    /**
     * @param string|string[]|null $body Raw string body (delimete multy line with array)
     */
    public function body($body): self
    {
        $body ??= [];

        $this->body = is_array($body)
            ? $body
            : [$body];

        return $this;
    }
}
