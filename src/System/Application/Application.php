<?php

declare(strict_types=1);

namespace System\Application;


use System\Support\Path;
use System\Support\Singleton\SingletonTrait;

class Application extends AbstractApplication
{
    use SingletonTrait;

    /**
     * Contractor.
     *
     * @param string|null $basePath application path
     * @return void
     */
    private function __construct(?string $basePath = null)
    {
        parent::__construct($basePath);
    }

    /**
     * {@inheritdoc}
     */
    public function bindPaths(): void
    {
        parent::bindPaths();
    }

    /**
     * {@inheritdoc}
     */
    public function getAppPath(string $path = ''): string
    {
        return $this->joinPaths($this->appPath ?: $this->basePath('app'), $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getBinPath(string $path = ''): string
    {
        return $this->joinPaths($this->binPath ?: $this->basePath('bin'), $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getApplicationCachePath(string $path = ''): string
    {
        return $this->joinPaths($this->appCachePath ?: $this->basePath('bootstrap/cache'), $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigPath(string $path = ''): string
    {
        return $this->joinPaths($this->configPath ?: $this->basePath('config'), $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabasePath(string $path = ''): string
    {
        return $this->joinPaths($this->databasePath ?: $this->basePath('database'), $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getPublicPath(string $path = ''): string
    {
        return $this->joinPaths($this->publicPath ?: $this->basePath('public'), $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getResourcesPath(string $path = ''): string
    {
        return $this->joinPaths($this->resourcesPath ?: $this->basePath('resources'), $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutesPath(string $path = ''): string
    {
        return $this->joinPaths($this->routesPath ?: $this->basePath('routes'), $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoragePath(string $path = ''): string
    {
        return $this->joinPaths($this->storagePath ?: $this->basePath('storage'), $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getTestPath(string $path = ''): string
    {
        return $this->joinPaths($this->testPath ?: $this->basePath('tests'), $path);
    }

    /**
     * {@inheritdoc}
     */
    public function getVendorPath(string $path = ''): string
    {
        return $this->joinPaths($this->vendorPath ?: $this->basePath('vendor'), $path);
    }

    /**
     * Get view paths.
     *
     * @param string $path Holds the arbitrary path.
     * @return string[] Return an array of paths for the views directory.
     */
    public function getViewPaths(string $path = ''): array
    {
        return Path::getPaths(['resources.views' . ($path !== '' ? ".$path" : '')]);
    }

    /**
     * {@inheritdoc}
     */
    public function setAppPath(string $path): self
    {
        $this->appPath = $path;

        $this->instance('path.app', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setBinPath(string $path): self
    {
        $this->binPath = $path;

        $this->instance('path.bin', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setApplicationCachePath(string $path): self
    {
        $this->appCachePath = $path;

        $this->instance('path.application.cache', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfigPath(string $path): self
    {
        $this->configPath = $path;

        $this->instance('path.config', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDatabasePath(string $path): self
    {
        $this->databasePath = $path;

        $this->instance('path.database', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPublicPath(string $path): self
    {
        $this->publicPath = $path;

        $this->instance('path.public', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setResourcesPath(string $path): self
    {
        $this->resourcesPath = $path;

        $this->instance('path.resources', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRoutesPath(string $path): self
    {
        $this->routesPath = $path;

        $this->instance('path.routes', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setStoragePath(string $path): self
    {
        $this->storagePath = $path;

        $this->instance('path.storage', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setTestPath(string $path): self
    {
        $this->testPath = $path;

        $this->instance('path.test', $path);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setVendorPath(string $path): self
    {
        $this->vendorPath = $path;

        $this->instance('path.vendor', $path);

        return $this;
    }
}
