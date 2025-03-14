<?php

declare(strict_types=1);

use System\Container\ServiceProvider\AbstractServiceProvider;

class TestServiceProvider extends AbstractServiceProvider
{
    public function register()
    {
        $ping = $this->app->get('ping');
        $this->app->set('ping', $ping);
    }
}
