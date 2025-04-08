<?php

namespace System\Controller;

use System\Http\Response\Response;

interface ControllerInterface
{
    /**
     * @param string               $view
     * @param array<string, mixed> $portal
     * @return Response
     */
    public static function renderView(string $view, array $portal = []): Response;

    /**
     * @param string $view
     * @return bool
     */
    public static function viewExists(string $view): bool;
}
