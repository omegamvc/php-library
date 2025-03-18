<?php

/**
 * Part of Omega - Application Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace System\Application;

use System\Application\Exceptions\MissingInstalledFileException;

use function array_key_exists;
use function file_exists;
use function file_put_contents;
use function filemtime;
use function is_writable;
use function time;
use function var_export;

use const PHP_EOL;

/**
 * The PackageManifest class is responsible for managing and caching the application package manifest.
 * It interacts with the Composer's installed package data to build and store a cache of relevant package
 * information, including providers defined in the `extra` field of the package's metadata.
 *
 * The class provides methods to access cached package data, regenerate the cache when necessary, and
 * ensure the integrity of the cached manifest. It is primarily used to store and retrieve configuration
 * settings related to the application’s dependencies, avoiding the need to recompute this data repeatedly.
 *
 * Key functionalities include:
 * - Reading the cached package manifest.
 * - Building the package manifest by reading Composer's `installed.json`.
 * - Ensuring the manifest is up-to-date and avoiding unnecessary regeneration.
 * - Storing the manifest data in a PHP file to improve performance.
 *
 * The cached manifest contains an array of providers and other package-specific information.
 * The manifest is only regenerated if the cache is outdated or missing.
 *
 * @category  System
 * @package   Application
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   1.0.
 */
class PackageManifest
{
    /**
     * Cached package manifest data.
     *
     * This property stores the cached package manifest as an array. It holds a list of providers
     * and related configurations extracted from the Composer-installed packages. The manifest is
     * loaded only once and cached for subsequent access, improving performance.
     *
     * @var array<string, array<string, array<int, string>>>|null
     */
    public ?array $packageManifest = null;

    /**
     * PackageManifest constructor.
     *
     * Initializes the package manifest class with the necessary paths for the base directory,
     * the application cache path, and optionally, the vendor path. The vendor path defaults to
     * 'vendor/composer/' if not provided.
     *
     * @param string      $basePath             Holds the base path of the application.
     * @param string      $applicationCachePath Holds the path to store cached package data.
     * @param string|null $vendorPath           Holds the path to the vendor directory. Default is 'vendor/composer/'.
     * @return void
     */
    public function __construct(
        private readonly string $basePath,
        private readonly string $applicationCachePath,
        private ?string $vendorPath = null,
    ) {
        $this->vendorPath ??= DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'composer' . DIRECTORY_SEPARATOR;
    }

    /**
     * Retrieve providers from the cached package manifest.
     *
     * This method fetches the providers stored in the cached package manifest, which are
     * extracted from the 'providers' field of the manifest.
     *
     * @return string[] Return an array of providers.
     */
    public function providers(): array
    {
        return $this->config('providers');
    }

    /**
     * Retrieve specific configuration from the cached package manifest.
     *
     * This method searches for a specific key (e.g., 'providers') in the package manifest
     * and returns the corresponding values. It filters out empty values and returns a clean array.
     *
     * @param string $key Holds the key to search for in the manifest (e.g., 'providers').
     * @return string[] Return an array of values corresponding to the provided key.
     */
    protected function config(string $key): array
    {
        $manifest = $this->getPackageManifest();
        $result   = [];

        foreach ($manifest as $configuration) {
            if (array_key_exists($key, $configuration)) {
                $values = (array) $configuration[$key];
                foreach ($values as $value) {
                    if (false === empty($value)) {
                        $result[] = $value;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Get the cached package manifest, building it if necessary.
     *
     * This method returns the cached package manifest. If the manifest is not cached or is outdated,
     * it will trigger a rebuild by calling the `build()` method.
     *
     * @return array<string, array<string, array<int, string>>> Return the package manifest data.
     */
    protected function getPackageManifest(): array
    {
        if ($this->packageManifest) {
            return $this->packageManifest;
        }

        $manifestPath = $this->applicationCachePath . 'packages.php';

        if (false === file_exists($manifestPath) || $this->isManifestOutdated($manifestPath)) {
            $this->build();
        }

        return $this->packageManifest = require $manifestPath;
    }

    /**
     * Check if the cached manifest is outdated based on its last modification time.
     *
     * This method compares the last modification time of the cached manifest file with the current
     * time. If the manifest is older than the defined threshold (e.g., 1 hour), it is considered outdated.
     *
     * @param string $manifestPath Holds the path to the cached package manifest.
     * @return bool Returns true if the manifest is outdated or does not exist, otherwise false.
     */
    protected function isManifestOutdated(string $manifestPath): bool
    {
        if (!file_exists($manifestPath) || !is_writable($manifestPath)) {
            return true;
        }

        $lastModifiedTime = filemtime($manifestPath);
        $threshold        = 3600;

        return (time() - $lastModifiedTime) > $threshold;
    }

    /**
     * Build the cached package manifest from Composer's installed packages.
     *
     * This method reads the Composer-installed package data from the 'installed.json' file,
     * extracts relevant package information (such as 'extra.omega'), and generates a new manifest
     * that is then cached in a PHP file.
     *
     * @return void
     * @throws MissingInstalledFileException if the file `vendor/composer/installed.php` not exists.
     */
    public function build(): void
    {
        $installedFile = $this->basePath . $this->vendorPath . 'installed.php';
        $packageFile   = $this->applicationCachePath . 'packages.php';

        if (!file_exists($installedFile)) {
            throw new MissingInstalledFileException(
                "The file {$this->vendorPath}installed.php does not exist.
                Try regenerating it with the command: composer dump-autoload -o"
            );
        }

        $installed = require $installedFile;

        $packageData = [
            'root'     => [
                'name'         => $installed['root']['name'] ?? 'unknown',
                'version'      => $installed['root']['version'] ?? 'unknown',
                'install_path' => $installed['root']['install_path'] ?? $this->basePath,
            ],
            'packages' => [],
        ];

        foreach ($installed['versions'] as $name => $data) {
            if (!isset($data['install_path'])) {
                continue;
            }

            $packageData['packages'][$name] = [
                'version'      => $data['version'] ?? 'unknown',
                'install_path' => $data['install_path'],
            ];
        }

        if (is_writable($this->applicationCachePath)) {
            file_put_contents(
                $packageFile,
                '<?php return ' . var_export($packageData, true) . ';' . PHP_EOL
            );
        }

        $this->packageManifest = $packageData;
    }
}
