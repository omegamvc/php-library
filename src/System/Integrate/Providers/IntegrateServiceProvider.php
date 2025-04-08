<?php

declare(strict_types=1);

namespace System\Integrate\Providers;

use System\Http\Upload\UploadFile;
use System\Http\Request\Request;
use System\Integrate\ServiceProvider;
use Validator\Validator;

class IntegrateServiceProvider extends ServiceProvider
{
    /**
     * {@inheritDoc}
     */
    public function register()
    {
        Request::macro(
            'validate',
            fn (?\Closure $rule = null, ?\Closure $filter = null) => Validator::make($this->{'all'}(), $rule, $filter)
        );

        Request::macro(
            'upload',
            function ($file_name) {
                $files = $this->{'getFile'}();

                return new UploadFile($files[$file_name]);
            }
        );
    }
}
