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

use function shell_exec;

/**
 * Class ServeCommand
 *
 * This class provides a command to start a local PHP server for serving the application.
 * It allows users to specify a custom port and optionally expose the server to the public network.
 * By default, the server runs on port 8080 and is bound to the localhost.
 *
 * @category   System
 * @package    Console
 * @subpackage Commands
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 *
 * @property string $port
 * @property bool   $expose
 */
class ServeCommand extends Command
{
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
            'pattern' => 'serve',
            'fn'      => [ServeCommand::class, 'main'],
            'default' => [
                'port'   => 8080,
                'expose' => false,
            ],
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
                'serve' => 'Serve server with port number (default 8080)',
            ],
            'options'   => [
                '--port'   => 'Serve with costume port',
                '--expose' => 'Make server run public network',
            ],
            'relation'  => [
                'serve' => ['--port', '--expose'],
            ],
        ];
    }
    /**
     * Starts the PHP built-in server and prints the relevant server addresses.
     *
     * This method runs the PHP server, displays the local address (e.g., http://localhost:8080),
     * and, if the `--expose` option is specified, it also displays the network address (e.g., http://192.168.x.x:8080).
     * The server runs on the specified port, defaulting to port 8080.
     *
     * @return void
     */
    public function main(): void
    {
        $port    = $this->port;
        $localIP = gethostbyname(gethostname());

        $print = new Style('Server running add:');

        $print
            ->newLines()
            ->push('Local')->tabs()->push("http://localhost:$port")->textBlue();

        if ($this->expose) {
            $print->newLines()->push('Network')->tabs()->push("http://$localIP:$port")->textBlue();
        }

        $print
            ->newLines(2)
            ->push('ctrl+c to stop server')
            ->newLines()
            ->tap(Alert::render()->info('server running...'))
            ->out(false);

        $address = $this->expose ? '0.0.0.0' : '127.0.0.1';

        shell_exec("php -S {$address}:{$port} -t public/");
    }
}
