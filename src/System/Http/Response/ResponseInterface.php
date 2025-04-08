<?php

namespace System\Http\Response;

use Exception;
use System\Http\Request\Request;

interface ResponseInterface
{
    /**
     * Send data to client.
     *
     * @return self
     */
    public function send(): self;

    /**
     * Send data to client with json format.
     *
     * @param string|array|null $content Content to send data
     * @return self
     */
    public function json(array|string $content = null): self;

    /**
     * Send data to client with html format.
     *
     * @param bool $minify If true html tag will be send minify
     * @return self
     */
    public function html(bool $minify = false): self;

    /**
     * Send data to client with plan format.
     *
     * @return self
     */
    public function planText(): self;

    /**
     * Its instant of exit application.
     *
     * @return void
     */
    public function close(): void;

    /**
     * Set Content.
     *
     * @param string|array $content Raw Content
     * @return self
     */
    public function setContent(array|string $content): self;

    /**
     * Set response code (override).
     *
     * @param int $responseCode
     * @return self
     */
    public function setResponseCode(int $responseCode): self;

    /**
     * Set header pools (override).
     *
     * @param array<string, string> $headers
     * @return self
     * @throws Exception
     */
    public function setHeaders(array $headers): self;

    /**
     * Set http protocol version.
     *
     * @param string $version
     * @return $this
     */
    public function setProtocolVersion(string $version): self;

    /**
     * Remove header from origin header.
     *
     * @param array<int, string> $headers
     * @return self
     * @deprecated use headers property instead
     *
     */
    public function removeHeader(array $headers = []): self;

    /**
     * Add new header to headers pools.
     *
     * @param string $header
     * @param ?string $value
     * @return self
     * @throws Exception
     * @deprecated use headers property instead
     *
     */
    public function header(string $header, ?string $value = null): self;

    /**
     * Get entry header.
     *
     * @return array<string, string>
     * @deprecated use headers property instead
     *
     */
    public function getHeaders(): array;

    public function getStatusCode(): int;

    /**
     * @return string|array
     */
    public function getContent(): array|string;

    /**
     * Get http protocol version.
     *
     * @return string
     */
    public function getProtocolVersion(): string;

    /**
     * Prepare response to send header to client.
     *
     * The response header will follow response request
     *
     * @param Request $request Http Web Request
     * @param array<int, string> $header_name Response header will be follow from request
     *
     * @return self
     */
    public function followRequest(Request $request, array $header_name = []): self;

    /**
     * Informational status code 1xx.
     *
     * @return bool
     */
    public function isInformational(): bool;

    /**
     * Successful status code 2xx.
     *
     * @return bool
     */
    public function isSuccessful(): bool;

    /**
     * Redirection status code 3xx.
     *
     * @retunr bool
     */
    public function isRedirection(): bool;

    /**
     * Client error status code 4xx.
     *
     * @return bool
     */
    public function isClientError(): bool;

    /**
     * Server error status code 5xx.
     *
     * @return bool
     */
    public function isServerError(): bool;
}
