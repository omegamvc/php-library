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

namespace System\Console\Commands;

use System\Console\Command;
use System\Console\Style\Alert;
use System\Console\Style\Style;
use System\Console\Traits\PrintHelpTrait;
use System\Router\Router;
use System\Text\Str;

use function count;
use function is_array;
use function strtoupper;
use function System\Console\style;

/**
 * Class RouteCommand
 *
 * This class is responsible for managing the routes within the application. It allows users to get a list of
 * the application's defined routes and their associated methods and expressions. The command `route:list` is
 * registered and provides an overview of all the registered routes in the application.
 *
 * @category   System
 * @package    Console
 * @subpackage Commands
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
class RouteCommand extends Command
{
    // use CommandTrait;
    use PrintHelpTrait;

    /**
     * Command registration details.
     *
     * This array defines the commands available for managing the application's
     * maintenance mode. Each command is associated with a pattern and a function
     * that handles the command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'cmd' => 'route:list',
            'fn'  => [RouteCommand::class, 'main'],
        ],
    ];

    /**
     * Provides help documentation for the command.
     *
     * This method returns an array with information about available commands
     * and options. It describes the two main commands (`down` and `up`) for
     * managing maintenance mode.
     *
     * @return array<string, array<string, string|string[]>>
     */
    public function printHelp(): array
    {
        return [
            'commands'  => [
                'route:list' => 'Get route list information',
            ],
            'options'   => [],
            'relation'  => [],
        ];
    }

    /**
     * Displays the list of registered routes.
     *
     * This method retrieves all the routes from the router and displays their methods,
     * names, and expressions in a formatted output.
     *
     * @return int Returns 0 after printing the route list.
     */
    public function main(): int
    {
        $print = new Style();
        $print->tap(Alert::render()->ok('route list'));
        foreach (Router::getRoutes() as $key => $route) {
            $method = $this->methodToStyle($route['method']);
            $name   = style($route['name'])->textWhite();
            $length = $method->length() + $name->length();

            $print
              ->tap($method)
              ->push(' ')
              ->tap($name)
              ->repeat('.', 80 - $length)->textDim()
              ->push(' ')
              ->push(Str::limit($route['expression'], 30))
              ->newLines()
            ;
        }
        $print->out();

        return 0;
    }

    /**
    /**
     * Converts HTTP methods to a styled format.
     *
     * This method takes a single HTTP method or an array of methods and returns a `Style`
     * object with appropriate color coding for each method (e.g., GET in blue, POST in yellow).
     *
     * @param string|string[] $methods The HTTP method or array of methods to style.
     * @return Style Returns a `Style` object with the appropriate color coding for the methods.
     */
    private function methodToStyle(string|array $methods): Style
    {
        if (is_array($methods)) {
            $group  = new Style();
            $length = count($methods);
            for ($i = 0; $i < $length; $i++) {
                $group->tap($this->coloringMethod($methods[$i]));
                if ($i < $length - 1) {
                    $group->push('|')->textDim();
                }
            }

            return $group;
        }

        return $this->coloringMethod($methods);
    }

    /**
     * Colors an HTTP method.
     *
     * This method takes an HTTP method string (e.g., GET, POST) and returns a `Style` object with
     * a color applied to the method. The colors are blue for GET, yellow for POST/PUT, and red for DELETE.
     *
     * @param string $method The HTTP method to color.
     * @return Style Returns a `Style` object with the appropriate color applied.
     */
    private function coloringMethod(string $method): Style
    {
        $method = strtoupper($method);

        if ($method === 'GET') {
            return (new Style($method))->textBlue();
        }

        if ($method === 'POST' || $method === 'PUT') {
            return (new Style($method))->textYellow();
        }

        if ($method === 'DELETE') {
            return (new Style($method))->textRed();
        }

        return (new Style($method))->textDim();
    }
}
