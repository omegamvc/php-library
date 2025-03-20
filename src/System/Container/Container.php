<?php

/**
 * Part of Omega - Container Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace System\Container;

use ArrayAccess;
use DI\Container as DIContainer;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;

use function array_key_exists;
use function sprintf;

/**
 * Dependency Injection Container with alias support and array access.
 *
 * This class extends `DIContainer` and implements `ArrayAccess` to provide a flexible and powerful
 * dependency injection container. It supports aliasing, allowing multiple names to refer to the same
 * entry, and provides an intuitive way to register, retrieve, and manage dependencies.
 *
 * @category  System
 * @package   Container
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 *
 * @implements ArrayAccess<string|class-string<mixed>, mixed>
 */
class Container extends DIContainer implements ArrayAccess
{
    /**
     * @var array<string, string> Maps alias names to their corresponding container entries.
     *
     * This array allows defining alternative names (aliases) for container entries,
     * enabling more flexible dependency resolution.
     */
    protected array $aliases = [];

    /**
     * Returns an entry of the container by its name.
     *
     * @template T
     * @param string|class-string<T> $id Entry name or a class name.
     * @return mixed|T
     * @throws DependencyException Error while resolving the entry.
     * @throws NotFoundException No entry found for the given name.
     */
    public function get(string $id): mixed
    {
        $id = $this->getAlias($id);

        return parent::get($id);
    }

    /**
     * Build an entry of the container by its name.
     *
     * This method behave like get() except resolves the entry again every time.
     * For example if the entry is a class then a new instance will be created each time.
     *
     * This method makes the container behave like a factory.
     *
     * @template T
     * @param string|class-string<T> $name       Entry name or a class name.
     * @param array                  $parameters Optional parameters to use to build the entry. Use this to force
     *                                           specific parameters to specific values. Parameters not defined in this
     *                                           array will be resolved using the container.
     * @return mixed|T
     * @throws DependencyException Error while resolving the entry.
     * @throws NotFoundException No entry found for the given name.
     */
    public function make(string $name, array $parameters = []): mixed
    {
        $name = $this->getAlias($name);

        return parent::make($name, $parameters);
    }
    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     * @return bool Return true if the identifier exists, false if not.
     */
    public function has(string $id): bool
    {
        $id = $this->getAlias($id);

        return parent::has($id);
    }

    /**
     * Registers an alias for a given abstract type in the container.
     *
     * This method allows associating an alias with an existing entry in the container.
     * Once an alias is set, it can be used interchangeably with the original identifier.
     *
     * @param string $abstract The original identifier of the container entry.
     * @param string $alias    The alias to associate with the given identifier.
     * @return void
     * @throws Exception If an alias is set to itself, an exception is thrown to prevent infinite recursion.
     */
    public function alias(string $abstract, string $alias): void
    {
        if ($abstract === $alias) {
            throw new Exception(
                sprintf(
                    "%s is aliased to itself.",
                    $abstract
                )
            );
        }

        $this->aliases[$alias] = $abstract;
    }

    /**
     * Resolves the alias for a given abstract type, if an alias exists.
     *
     * If the provided identifier has been registered as an alias, this method recursively
     * resolves it to its original entry name. If no alias is found, the original identifier
     * is returned unchanged.
     *
     * @param string $abstract The identifier or alias to resolve.
     * @return string The resolved identifier in the container.
     */
    public function getAlias(string $abstract): string
    {
        return array_key_exists($abstract, $this->aliases)
            ? $this->getAlias($this->aliases[$abstract])
            : $abstract;
    }

    /**
     * Clears all registered aliases and resolved entries in the container.
     *
     * This method resets the container, removing all registered aliases and clearing
     * the cache of resolved dependencies. It ensures that future calls to `get()` or `make()`
     * will resolve dependencies from scratch.
     *
     * @return void
     */
    public function flush(): void
    {
        $this->aliases              = [];
        $this->resolvedEntries      = [];
        $this->entriesBeingResolved = [];
    }

    /**
     * Checks if a given entry exists in the container.
     *
     * This method allows checking if a dependency is registered in the container
     * using array-style access.
     *
     * @param string $offset The identifier to check.
     * @return bool True if the entry exists, false otherwise.
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Get the value.
     *
     * @param string|class-string<mixed> $offset entry name or a class name
     * @return mixed
     * @throws DependencyException Error while resolving the entry.
     * @throws NotFoundException No entry found for the given name.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->make($offset);
    }

    /**
     * Registers a new entry in the container.
     *
     * This method allows adding a new dependency to the container using array-style syntax.
     *
     * @param string $offset The identifier for the entry.
     * @param mixed  $value  The value or instance to bind to the container.
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Removes an entry from the resolved container cache.
     *
     * This method allows unsetting a registered dependency using array-style syntax.
     * Note: This does not remove the binding itself, only the resolved instance.
     *
     * @param string $offset The identifier of the entry to remove.
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->resolvedEntries[$offset]);
    }
}
