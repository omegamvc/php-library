<?php

declare(strict_types=1);

namespace System\Console\Commands;

use System\Console\Command;
use System\Console\Style\Alert;
use System\Console\Style\Style;
use System\Console\Traits\PrintHelpTrait;
use System\Router\Router;
use System\Text\Str;

use function count;
use function strtoupper;
use function System\Console\style;

class RouteCommand extends Command
{
    // use CommandTrait;
    use PrintHelpTrait;

    /**
     * Register command.
     *
     * @var array<int, array<string, mixed>>
     */
    public static array $command = [
        [
            'cmd' => 'route:list',
            'fn' => [RouteCommand::class, 'main'],
        ],
    ];

    /**
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
     * @return int
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
     * @param string|string[] $methods
     * @return Style
     */
    private function methodToStyle(array|string $methods): Style
    {
        if (is_array($methods)) {
            $group  = new Style();
            $length = count($methods);
            for ($i=0; $i < $length; $i++) {
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
     * @param string $method
     * @return Style
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
