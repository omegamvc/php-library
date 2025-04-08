<?php

declare(strict_types=1);

namespace System\Http\Response;

use System\Http\Exceptions\StreamedResponseCallableException;
use System\Http\HeaderCollection;
use System\Http\Response\Response;

class StreamedResponse extends Response
{
    /** @var (callable(): void)|null */
    private mixed $callableStream;

    private bool $isStream;

    /**
     * Create new Stream Response.
     *
     * @param (callable(): void)|null $callableStream
     * @param int                     $responseCode    Response code
     * @param array<string, string>   $headers         Header send to client
     */
    public function __construct(
        mixed $callableStream,
        int   $responseCode = Response::HTTP_OK,
        array $headers = [],
    ) {
        $this->setStream($callableStream);
        $this->setResponseCode($responseCode);
        $this->headers   = new HeaderCollection($headers);
        $this->isStream = false;
    }

    /**
     * Set stream callback.
     *
     * @param (callable(): void)|null $callableStream
     */
    public function setStream(mixed $callableStream): self
    {
        $this->callableStream = $callableStream;

        return $this;
    }

    /**
     * {@inheritDoc}
     * @throws StreamedResponseCallableException
     */
    protected function sendContent(): void
    {
        if ($this->isStream) {
            return;
        }

        $this->isStream = true;

        if (null === $this->callableStream) {
            throw new StreamedResponseCallableException();
        }

        ($this->callableStream)();
    }
}
