<?php

declare(strict_types=1);

namespace System\Container\ServiceProvider;

use Closure;
use System\Http\Upload\UploadFile;
use System\Http\Request\Request;
use System\Container\ServiceProvider\AbstractServiceProvider;
use Validator\Validator;

class AdditionalServiceProvider extends AbstractServiceProvider
{
    /**
     * {@inheritDoc}
     */
    public function register(): void
    {
        Request::macro(
            'validate',
            fn (
                ?Closure $rule = null,
                ?Closure $filter = null
            ) => Validator::make(
                $this->{'all'}(),
                $rule,
                $filter
            )
        );

        Request::macro(
            'upload',
            function ($fileName) {
                $files = $this->{'getFile'}();

                return new UploadFile($files[$fileName]);
            }
        );
    }
}
