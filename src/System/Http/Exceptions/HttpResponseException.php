<?php

declare(strict_types=1);

namespace System\Http\Exceptions;

use RuntimeException;
use System\Http\Response;

class HttpResponseException extends RuntimeException
{
    /**
     * Creates a Response Exception.
     *
     * @param Response $response
     * @return void
     */
    public function __construct(protected Response $response)
    {
    }

    /**
     * Retrieves the response object.
     *
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}
