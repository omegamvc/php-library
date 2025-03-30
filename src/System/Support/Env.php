<?php

namespace System\Support;

use Closure;
use Dotenv\Repository\Adapter\PutenvAdapter;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Repository\RepositoryInterface;
use PhpOption\Option;
use PhpOption\Some;
use RuntimeException;

class Env
{
    /**
     * Indicates if the putenv adapter is enabled.
     *
     * @var bool
     */
    protected static bool $putenv = true;

    /**
     * The environment repository instance.
     *
     * @var RepositoryInterface|null
     */
    protected static ?RepositoryInterface $repository = null;

    /**
     * The list of custom adapters for loading environment variables.
     *
     * @var array<Closure>
     */
    protected static array $customAdapters = [];

    /**
     * Enable the putenv adapter.
     *
     * @return void
     */
    public static function enablePutenv(): void
    {
        static::$putenv     = true;
        static::$repository = null;
    }

    /**
     * Disable the putenv adapter.
     *
     * @return void
     */
    public static function disablePutenv(): void
    {
        static::$putenv     = false;
        static::$repository = null;
    }

    /**
     * Register a custom adapter creator Closure.
     *
     * @param Closure     $callback Holds the custom adapter callback.
     * @param string|null $name     Holds the custom adapter name.
     * @return void
     */
    public static function extend(Closure $callback, ?string $name = null): void
    {
        if (! is_null($name)) {
            static::$customAdapters[$name] = $callback;
        } else {
            static::$customAdapters[] = $callback;
        }
    }

    /**
     * Get the environment repository instance.
     *
     * @return RepositoryInterface
     */
    public static function getRepository(): RepositoryInterface
    {
        if (static::$repository === null) {
            $builder = RepositoryBuilder::createWithDefaultAdapters();

            if (static::$putenv) {
                $builder = $builder->addAdapter(PutenvAdapter::class);
            }

            foreach (static::$customAdapters as $adapter) {
                $builder = $builder->addAdapter($adapter());
            }

            static::$repository = $builder->immutable()->make();
        }

        return static::$repository;
    }

    /**
     * Get the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::getOption($key)->getOrCall(fn () => value($default));
    }

    /**
     * Get the value of a required environment variable.
     *
     * @param  string  $key
     * @return mixed
     * @throws RuntimeException if the environment variable has no value.
     */
    public static function getOrFail(string $key): mixed
    {
        return self::getOption($key)
            ->getOrThrow(
                new RuntimeException("Environment variable [$key] has no value."
                )
            );
    }

    /**
     * Get the possible option for this environment variable.
     *
     * @param  string  $key
     * @return Option|Some
     */
    protected static function getOption(string $key): Option|Some
    {
        return Option::fromValue(static::getRepository()->get($key))
            ->map(function ($value) {
                switch (strtolower($value)) {
                    case 'true':
                    case '(true)':
                        return true;
                    case 'false':
                    case '(false)':
                        return false;
                    case 'empty':
                    case '(empty)':
                        return '';
                    case 'null':
                    case '(null)':
                        return;
                }

                if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
                    return $matches[2];
                }

                return $value;
            });
    }
}
