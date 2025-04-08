<?php

declare(strict_types=1);

namespace System\Controller;

use System\Http\Response\Response;

abstract class AbstractController implements ControllerInterface
{
    /**
     * @param string               $view
     * @param array<string, mixed> $portal
     * @return \System\Http\Response\Response
     */
    abstract public static function renderView(string $view, array $portal = []): Response;

    /**
     * @param string $view
     * @return bool
     */
    abstract public static function viewExists(string $view): bool;
}
