<?php

declare(strict_types=1);

namespace System\Http\Exceptions;

use System\Http\Response\Response;

class HttpResponseException extends \RuntimeException
{
    /**
     * Creates a Response Exception.
     *
     * @param \System\Http\Response\Response $response
     * @return void
     */
    public function __construct(protected Response $response)
    {
        parent::__construct('HTTP response exception', $response->getStatusCode());
    }

    /**
     * Get the Response object.
     *
     * @return Response Return the the Response object.
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}
