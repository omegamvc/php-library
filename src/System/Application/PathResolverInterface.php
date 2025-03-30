<?php

declare(strict_types=1);

namespace System\Application;

interface PathResolverInterface
{
    /**
     * Get the base path of the Laravel installation.
     *
     * @param  string  $path
     * @return string
     */
    public function basePath(string $path = ''): string;

    /**
     * Set the base path.
     *
     * @param string $basePath Holds the base path.
     * @return self
     */
    public function setBasePath(string $basePath): self;

    /**
     * Bind the application path with the container.
     *
     * @return void
     */
    public function bindPaths(): void;

    /**
     * Get the path to the app directory.
     *
     * @param  string  $path
     * @return string
     */
    public function getAppPath(string $path = ''): string;

    /**
     * Get the path to the bin directory.
     *
     * @param  string  $path
     * @return string
     */
    public function getBinPath(string $path = ''): string;

    /**
     * Get the path to the application cache directory.
     *
     * @param  string  $path
     * @return string
     */
    public function getApplicationCachePath(string $path = ''): string;

    /**
     * Get the path to the config directory.
     *
     * @param  string  $path
     * @return string
     */
    public function getConfigPath(string $path = ''): string;

    /**
     * Get the path to the database directory.
     *
     * @param  string  $path
     * @return string
     */
    public function getDatabasePath(string $path = ''): string;

    /**
     * Get the path to the public directory.
     *
     * @param  string  $path
     * @return string
     */
    public function getPublicPath(string $path = ''): string;

    /**
     * Get the path to the resources directory.
     *
     * @param  string  $path
     * @return string
     */
    public function getResourcesPath(string $path = ''): string;

    /**
     * Get the path to the routes directory.
     *
     * @param  string  $path
     * @return string
     */
    public function getRoutesPath(string $path = ''): string;

    /**
     * Get the path to the storage directory.
     *
     * @param  string  $path
     * @return string
     */
    public function getStoragePath(string $path = ''): string;

    /**
     * Get the path to the test directory.
     *
     * @param  string  $path
     * @return string
     */
    public function getTestPath(string $path = ''): string;

    /**
     * Get the path to the vendor directory.
     *
     * @param  string  $path
     * @return string
     */
    public function getVendorPath(string $path = ''): string;

    /**
     * Set the app directory.
     *
     * @param  string  $path
     * @return $this
     */
    public function setAppPath(string $path): self;

    /**
     * Set the bin directory.
     *
     * @param  string  $path
     * @return $this
     */
    public function setBinPath(string $path): self;

    /**
     * Set the application cache directory.
     *
     * @param  string  $path
     * @return $this
     */
    public function setApplicationCachePath(string $path): self;

    /**
     * Set the config directory.
     *
     * @param  string  $path
     * @return $this
     */
    public function setConfigPath(string $path): self;

    /**
     * Set the database directory.
     *
     * @param  string  $path
     * @return $this
     */
    public function setDatabasePath(string $path): self;

    /**
     * Set the public directory.
     *
     * @param  string  $path
     * @return $this
     */
    public function setPublicPath(string $path): self;

    /**
     * Set the resources directory.
     *
     * @param  string  $path
     * @return $this
     */
    public function setResourcesPath(string $path): self;

    /**
     * Set the routes directory.
     *
     * @param  string  $path
     * @return $this
     */
    public function setRoutesPath(string $path): self;

    /**
     * Set the storage directory.
     *
     * @param  string  $path
     * @return $this
     */
    public function setStoragePath(string $path): self;


    /**
     * Set the tests directory.
     *
     * @param  string  $path
     * @return $this
     */
    public function setTestPath(string $path): self;

    /**
     * Set the vendor directory.
     *
     * @param  string  $path
     * @return $this
     */
    public function setVendorPath(string $path): self;
}
