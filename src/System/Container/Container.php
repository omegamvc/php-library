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
use DI\NotFoundException;
use Exception;
use System\Container\Exception\AliasConflictException;
use System\Container\Exception\AliasLoopException;
use System\Container\Exception\DependencyResolutionException;
use System\Container\Exception\ServiceNotFoundException;

/**
 * Container class responsible for managing service entries and their aliases.
 *
 * The Container is a dependency injection container that allows services to be
 * registered, aliased, resolved, and accessed. It supports aliasing services,
 * checking for service existence, and resolving dependencies dynamically.
 * This container also implements `ArrayAccess` to provide an array-like interface
 * for service management.
 *
 * @category  Omega
 * @package   Container
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 *
 * @implements ArrayAccess<string|class-string<mixed>, mixed>
 */
class Container extends DIContainer implements ArrayAccess
{
    /** @var array<string, string> Registered aliases entry container. */
    protected array $aliases = [];

    /** @var array<string, bool> Stack of aliases being resolved to detect circular alias references. */
    protected array $aliasStack = [];

    /**
     * Container constructor.
     *
     * Initializes the container and calls the parent constructor.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Retrieves a service from the container.
     *
     * This method resolves any aliases and fetches the corresponding service
     * entry. If the service cannot be found or has unresolved dependencies,
     * exceptions will be thrown.
     *
     * @param string $id The ID or name of the service to retrieve.
     * @return mixed The resolved service instance.
     * @throws ServiceNotFoundException If the service is not found in the container.
     * @throws DependencyResolutionException If there is an issue resolving the service's dependencies.
     * @throws Exception For other general errors during service resolution.
     */
    public function get(string $id): mixed
    {
        try {
            $id = $this->getAlias($id);
            return parent::get($id);
        } catch (NotFoundException $e) {
            throw new ServiceNotFoundException($id);
        } catch (Exception $e) {
            throw new DependencyResolutionException(
                "Unable to retrieve entry: "
                . $id
                . "."
                . $e->getMessage());
        }
    }

    /**
     * Creates a service instance with optional parameters.
     *
     * This method will resolve the service and its dependencies, injecting
     * parameters if provided. Any errors in the resolution process will be
     * thrown as exceptions.
     *
     * @param string $name The name of the service to create.
     * @param array<array-key, mixed> $parameters Optional parameters to override the default ones.
     * @return mixed The resolved service instance.
     * @throws ServiceNotFoundException If the service cannot be found.
     * @throws DependencyResolutionException If there is an issue resolving the service's dependencies.
     * @throws Exception For other general errors during service creation.
     */
    public function make(string $name, array $parameters = []): mixed
    {
        try {
            $name = $this->getAlias($name);
            return parent::make($name, $parameters);
        } catch (NotFoundException $e) {
            throw new ServiceNotFoundException($name);
        } catch (Exception $e) {
            throw new DependencyResolutionException("Unable to make entry: ". $name . "." .  $e->getMessage());
        }
    }

    /**
     * Checks if a service exists in the container.
     *
     * This method checks if a service with the given ID is registered in the container.
     * It also resolves any aliases before checking.
     *
     * @param string $id The ID or name of the service to check.
     * @return bool Returns `true` if the service exists, `false` otherwise.
     */
    public function has(string $id): bool
    {
        $id = $this->getAlias($id);
        return parent::has($id);
    }

    /**
     * Registers an alias for a service.
     *
     * This method allows you to create an alias for a service in the container.
     * If the alias conflicts with the abstract service name, an exception will be thrown.
     *
     * @param string $abstract The abstract name of the service to alias.
     * @param string $alias The alias to assign to the service.
     * @return void
     * @throws AliasConflictException If the alias conflicts with the abstract service name.
     */
    public function alias(string $abstract, string $alias): void
    {
        if ($abstract === $alias) {
            throw new AliasConflictException($abstract);
        }

        $this->aliases[$alias] = $abstract;
    }

    /**
     * Resolves an alias to the actual abstract service name.
     *
     * This method checks if an alias is registered for the given abstract service
     * name and returns the corresponding service name. It also checks for circular
     * alias references and throws an exception if detected.
     *
     * @param string $abstract The abstract name of the service to resolve.
     * @return string The resolved service name.
     * @throws AliasLoopException If a circular alias reference is detected.
     */
    public function getAlias(string $abstract): string
    {
        if (isset($this->aliasStack[$abstract])) {
            throw new AliasLoopException($abstract);
        }

        $this->aliasStack[$abstract] = true;

        $resolved = $this->aliases[$abstract] ?? $abstract;

        unset($this->aliasStack[$abstract]);

        return $resolved;
    }

    /**
     * Clears all services and aliases from the container.
     *
     * This method flushes the container, clearing all resolved entries,
     * alias mappings, and entries that are being resolved.
     *
     * @return void
     */
    public function flush(): void
    {
        $this->aliases = [];
        $this->resolvedEntries = [];
        $this->entriesBeingResolved = [];
    }

    /**
     * Checks if an entry exists in the container (ArrayAccess implementation).
     *
     * This method is part of the `ArrayAccess` interface. It checks if the given
     * offset (service name) exists in the container.
     *
     * @param string $offset The service name to check.
     * @return bool `true` if the service exists, `false` otherwise.
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Retrieves an entry from the container (ArrayAccess implementation).
     *
     * This method is part of the `ArrayAccess` interface. It allows accessing services
     * using array syntax. It resolves the service and returns its instance.
     *
     * @param string|class-string<mixed> $offset The service name or class name.
     * @return mixed The resolved service instance.
     * @throws ServiceNotFoundException If the service cannot be found.
     * @throws DependencyResolutionException If there is an issue resolving the service's dependencies.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Sets a service in the container (ArrayAccess implementation).
     *
     * This method is part of the `ArrayAccess` interface. It allows setting a service
     * using array syntax.
     *
     * @param string $offset The service name.
     * @param mixed $value The service instance to store.
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Unsets a service from the container (ArrayAccess implementation).
     *
     * This method is part of the `ArrayAccess` interface. It allows unsetting a
     * service using array syntax, removing it from the resolved entries.
     *
     * @param string $offset The service name to remove.
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->resolvedEntries[$offset]);
    }

    /**
     * Registra una chiave solo se non è già stata impostata.
     *
     * @param string $id
     * @param mixed $value
     * @return void
     */
    public function instance(string $id, mixed $value): void
    {
        if (!$this->has($id)) {
            $this->set($id, $value);
        }
    }

    /**
     * Registrazione di una chiave con un metodo setter personalizzato (es. databasePath)
     *
     * @param string $id
     * @param callable $setter
     * @return void
     */
    public function setWithSetter(string $id, callable $setter): void
    {
        $this->instance($id, $setter());
    }
}
