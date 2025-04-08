<?php

declare(strict_types=1);

namespace System\Http\Exceptions;

use RuntimeException;
use Throwable;

class HttpException extends \RuntimeException
{
    /**
     * Http status code.
     */
    private int $statusCode;

    /**
     * Http Headers information.
     *
     * @var array<string, string>
     */
    private array $headers;

    /**
     * @param int                   $statusCode
     * @param string                $message
     * @param ?Throwable            $previous
     * @param array<string, string> $headers
     * @param int                   $code
     * @return void
     */
    public function __construct(
        int $statusCode,
        string $message,
        ?Throwable $previous = null,
        array $headers = [],
        int $code = 0,
    ) {
        $this->statusCode = $statusCode;
        $this->headers    = $headers;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the status code.
     *
     * @return int Return the status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get Http Header.
     *
     * @return array<string, string> Return the HTTP header.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
