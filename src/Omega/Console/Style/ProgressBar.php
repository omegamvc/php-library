<?php

declare(strict_types=1);

namespace Omega\Console\Style;

use Omega\Console\Traits\CommandTrait;

use function array_key_exists;
use function array_keys;
use function array_map;
use function array_merge;
use function call_user_func;
use function call_user_func_array;
use function ceil;
use function str_pad;
use function str_repeat;
use function str_replace;

use const PHP_EOL;

class ProgressBar
{
    use CommandTrait;

    private string $template;
    public int $current = 0;
    public int $maks    = 1;

    private string $progress;

    /**
     * Callback when task was complete.
     *
     * @var callable(): string
     */
    public $complete;

    /**
     * Bind template.
     *
     * @var array<callable(int, int): string>
     */
    private array $binds;

    /**
     * Bind template.
     *
     * @var array<callable(int, int): string>
     */
    public static array $costume_binds = [];

    /**
     * @param array<callable(int, int): string> $binds
     */
    public function __construct(string $template = ':progress :percent', array $binds = [])
    {
        $this->progress = '';
        $this->template = $template;
        $this->complete = fn (): string => $this->complete();
        $this->binding($binds);
    }

    public function __toString()
    {
        $binds = array_map(function ($bind) {
            return call_user_func_array($bind, [
                $this->current,
                $this->maks,
            ]);
        }, $this->binds);

        return str_replace(array_keys($binds), $binds, $this->template);
    }

    public function tick(): void
    {
        $this->progress = (string) $this;
        (new Style())->replace($this->progress);

        if ($this->current + 1 > $this->maks) {
            $complete = (string) call_user_func($this->complete);
            (new Style())->clear();
            (new Style())->replace($complete . PHP_EOL);
        }
    }

    /**
     * Customize tick in progressbar.
     *
     * @param array<callable(int, int): string> $binds
     */
    public function tickWith(string $template = ':progress :percent', array $binds = []): void
    {
        $this->template = $template;
        $this->binding($binds);
        $this->progress = (string) $this;
        (new Style())->replace($this->progress);

        if ($this->current + 1 > $this->maks) {
            $complete = (string) call_user_func($this->complete);
            (new Style())->clear();
            (new Style())->replace($complete . PHP_EOL);
        }
    }

    private function progress(int $current, int $maks): string
    {
        $length = 20;
        $tick   = (int) ceil($current * ($length / $maks)) - 1;
        $head   = $current === $maks ? '=' : '>';
        $bar    = str_repeat('=', $tick) . $head;
        $left   = '-';

        return '[' . str_pad($bar, $length, $left) . ']';
    }

    /**
     * Binding.
     *
     * @param array<callable(int, int): string> $binds
     */
    public function binding(array $binds): void
    {
        $binds = array_merge($binds, self::$costume_binds);
        if (false === array_key_exists(':progress', $binds)) {
            $binds[':progress'] =  fn ($current, $maks): string => $this->progress($current, $maks);
        }

        if (false === array_key_exists(':percent', $binds)) {
            $binds[':percent'] =  fn ($current, $maks): string => ceil(($current / $maks) * 100) . '%';
        }
        $this->binds    = $binds;
    }

    private function complete(): string
    {
        return $this->progress;
    }
}
