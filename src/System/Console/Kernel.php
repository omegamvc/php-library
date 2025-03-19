<?php

/**
 * Part of Omega - Console Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace System\Console;

use DI\DependencyException;
use DI\NotFoundException;
use System\Console\Style\Style;
use System\Application\Application;
use System\Bootstrap\BootProviders;
use System\Config\ConfigProviders;
use System\Support\Facades\FacadeProviders;
use System\Container\ServiceProvider\RegisterProviders;

use function array_fill;
use function array_merge;
use function arsort;
use function explode;
use function floor;
use function is_int;
use function max;
use function min;
use function strlen;
use function strtolower;

/**
 * Kernel class.
 *
 * Kernel class handles the execution of commands and processes input arguments.
 * It manages the application bootstrap, command matching, and similarity checking for commands.
 * The class is responsible for executing commands, handling errors, and returning appropriate exit codes.
 *
 * @category  System
 * @package   Console
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version   2.0.0
 */
class Kernel
{
    /**
     * @var int The exit status code of the console.
     */
    protected int $exitCode;

    /**
     * @var array<int, class-string> A list of bootstrap providers for the application.
     */
    protected array $bootstrappers = [
        ConfigProviders::class,
        FacadeProviders::class,
        RegisterProviders::class,
        BootProviders::class,
    ];

    /**
     * Kernel constructor that sets the application instance.
     *
     * @param Application $app The application instance.
     * @return void
     */
    public function __construct(
        protected Application $app
    ) {
    }

    /**
     * Handles input arguments and processes commands.
     *
     * @param string|array<int, string> $arguments The input arguments (command-line arguments).
     * @return int The exit code.
     * @throws DependencyException If a required dependency is not found.
     * @throws NotFoundException If a command is not found.
     */
    public function handle(string|array $arguments): int
    {
        // handle command empty
        $baseArgs = $arguments[1] ?? '--help';
        $commands = [];

        $this->bootstrap();

        foreach ($this->commands() as $cmd) {
            $commands = array_merge($commands, $cmd->patterns(), $cmd->cmd());

            if ($cmd->isMatch($baseArgs)) {
                $class = $cmd->class();
                $this->app->set($class, fn () => new $class($arguments, $cmd->defaultOption()));

                $call = $this->app->call($cmd->call());

                return $this->exitCode = (is_int($call) ? $call : 0);
            }
        }

        // did you mean
        $count   = 0;
        $similar = (new Style('Did you mean?'))->textLightYellow()->newLines();
        foreach ($this->getSimilarity($baseArgs, $commands, 0.8) as $term => $score) {
            $similar->push('    > ')->push($term)->textYellow()->newLines();
            $count++;
        }

        // if command not register
        if (0 === $count) {
            (new Style())
                ->push('Command Not Found, run help command')->textRed()->newLines(2)
                ->push('> ')->textDim()
                ->push('php ')->textYellow()
                ->push('cli ')
                ->push('--help')->textDim()
                ->newLines()
                ->out()
            ;

            return $this->exitCode = 1;
        }

        $similar->out();

        return $this->exitCode = 1;
    }

    /**
     * Registers the application's bootstrap providers.
     *
     * @return void
     * @throws DependencyException If a required dependency is not found.
     * @throws NotFoundException If a required bootstrap provider is not found.
     */
    public function bootstrap(): void
    {
        $this->app->bootstrapWith($this->bootstrappers);
    }

    /**
     * Calls a command using its known signature.
     * The signature does not require the PHP prefix; use `handle` for better parsing.
     *
     * @param string                              $signature The command signature.
     * @param array<string, string|bool|int|null> $parameter Additional parameters for the command.
     * @return int The exit code.
     * @throws DependencyException If a required dependency is not found.
     * @throws NotFoundException If the command is not found.
     */
    public function call(string $signature, array $parameter = []): int
    {
        $arguments = explode(' ', $signature);
        $baseArgs  = $arguments[1] ?? '--help';

        $this->bootstrap();

        foreach ($this->commands() as $cmd) {
            if ($cmd->isMatch($baseArgs)) {
                $class = $cmd->class();
                $this->app->set($class, fn () => new $class($arguments, $parameter));

                $call = $this->app->call($cmd->call());

                return is_int($call) ? $call : 0;
            }
        }

        return 1;
    }

    /**
     * Returns similar commands based on a given string and threshold for similarity.
     *
     * @param string   $find      The search term to find similar commands.
     * @param string[] $commands  The list of available commands.
     * @param float    $threshold The similarity threshold (default: 0.8).
     * @return array<string, float> Sorted list of similar commands with similarity scores.
     */
    private function getSimilarity(string $find, array $commands, float $threshold = 0.8): array
    {
        $closest   = [];
        $findLower = strtolower($find);

        foreach ($commands as $command) {
            $commandLower = strtolower($command);
            $similarity   = $this->jaroWinkler($findLower, $commandLower);

            if ($similarity >= $threshold) {
                $closest[$command] = $similarity;
            }
        }

        arsort($closest);

        return $closest;
    }

    /**
     * Calculates the similarity score between two strings using the Jaro-Winkler distance.
     *
     * @param string $find    The first string.
     * @param string $command The second string.
     * @return float The similarity score between 0 and 1.
     */
    private function jaroWinkler(string $find, string $command): float
    {
        $jaro = $this->jaro($find, $command);

        // Calculate the prefix length (maximum of 4 characters)
        $prefixLength    = 0;
        $maxPrefixLength = min(strlen($find), strlen($command), 4);
        for ($i = 0; $i < $maxPrefixLength; $i++) {
            if ($find[$i] !== $command[$i]) {
                break;
            }
            $prefixLength++;
        }

        return $jaro + ($prefixLength * 0.1 * (1 - $jaro));
    }

    /**
     * Calculates the Jaro similarity score between two strings.
     *
     * @param string $find    The first string.
     * @param string $command The second string.
     * @return float The Jaro similarity score between 0 and 1.
     */
    private function jaro(string $find, string $command): float
    {
        $len1 = strlen($find);
        $len2 = strlen($command);

        if ($len1 === 0) {
            return $len2 === 0 ? 1.0 : 0.0;
        }

        $matchDistance = (int) floor(max($len1, $len2) / 2) - 1;

        $str1Matches = array_fill(0, $len1, false);
        $str2Matches = array_fill(0, $len2, false);

        $matches        = 0;
        $transpositions = 0;

        // Find matching characters
        for ($i = 0; $i < $len1; $i++) {
            $start = max(0, $i - $matchDistance);
            $end   = min($i + $matchDistance + 1, $len2);

            for ($j = $start; $j < $end; $j++) {
                if ($str2Matches[$j] || $find[$i] !== $command[$j]) {
                    continue;
                }
                $str1Matches[$i] = true;
                $str2Matches[$j] = true;
                $matches++;
                break;
            }
        }

        if ($matches === 0) {
            return 0.0;
        }

        // Count transpositions
        $k = 0;
        for ($i = 0; $i < $len1; $i++) {
            if (false === $str1Matches[$i]) {
                continue;
            }
            while (false === $str2Matches[$k]) {
                $k++;
            }
            if ($find[$i] !== $command[$k]) {
                $transpositions++;
            }
            $k++;
        }

        $transpositions /= 2;

        return (($matches / $len1) + ($matches / $len2) + (($matches - $transpositions) / $matches)) / 3.0;
    }

    /**
     * Returns the exit status code of the kernel.
     *
     * @return int The exit status code.
     */
    public function exitCode(): int
    {
        return $this->exitCode;
    }

    /**
     * Loads and returns the commands configuration.
     *
     * @return CommandMap[] An array of command maps loaded from the configuration.
     */
    protected function commands(): array
    {
        return Util::loadCommandFromConfig($this->app);
    }
}
