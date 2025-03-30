<?php

declare(strict_types=1);

namespace System\Application;

interface ApplicationInterface extends PathResolverInterface
{
    public const PROJECT_VERSION = '2.0.0';

    public const PROJECT_NAME = 'Omega';

    /**
     * Get the Omega version number or custom version number.
     *
     * @param string $projectVersion
     * @return string
     */
    public function getVersion(string $projectVersion = ''): string;

    /**
     * Get the Omega default name or a custom project name.
     *
     * @param string $projectName
     * @return string
     */
    public function getName(string $projectName = ''): string;

    /**
     * Detect application environment.
     *
     * @return string
     */
    public function getEnvironment(): string;

    /**
     * Detect application debug enable.
     *
     * @return bool
     */
    public function isDebugMode(): bool;

    /**
     * Detect application production mode.
     *
     * @return bool
     */
    public function isProduction(): bool;

    /**
     * Detect application development mode.
     *
     * @return bool
     */
    public function isDev(): bool;

    /**
     * Detect application has been booted.
     *
     * @return bool
     */
    public function isBooted(): bool;

    /**
     * Detect application has been bootstrapped.
     *
     * @return bool
     */
    public function isBootstrapped(): bool;

    /**
     * Bootstrapper.
     *
     * @param array<int, class-string> $bootstrappers
     * @return void
     */
    public function bootstrapWith(array $bootstrappers): void;

    /**
     * Boot service provider.
     *
     * @return void
     */
    public function bootProvider(): void;

    /**
     * Register service providers.
     *
     * @return void
     */
    public function registerProvider(): void;

    /**
     * Add booting call back, call before boot is calling.
     *
     * @param callable $callback
     * @return void
     */
    public function bootingCallback(callable $callback): void;

    /**
     * Add booted call back, call after boot is called.
     *
     * @param callable $callback
     * @return void
     */
    public function bootedCallback(callable $callback): void;

    /**
     * Flush or reset application (static).
     *
     * @return void
     */
    public function flush(): void;
}
