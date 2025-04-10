<?php

namespace System\Http\Request;


use ArrayAccess;
use Closure;
use Exception;
use IteratorAggregate;
use System\Collection\Collection;
use System\Collection\CollectionImmutable;
use System\Http\Upload\UploadFile;
use Traversable;
use System\Validator\Validator;

/**
 * @method Validator  validate(?Closure $rule = null, ?Closure $filter = null)
 * @method UploadFile upload(array $file_name)
 *
 * @implements ArrayAccess<string, string>
 * @implements IteratorAggregate<string, string>
 */
interface RequestInterface
{
    /**
     * Initial request.
     *
     * @param string $url
     * @param array<string, string> $query
     * @param array<string, string> $post
     * @param array<string, string> $attributes
     * @param array<string, string> $cookies
     * @param array<string, string> $files
     * @param array<string, string> $headers
     * @param string $method
     * @param string $remoteAddress
     * @param string|null $rawBody
     * @return self
     */
    public function initialize(
        string $url,
        array $query = [],
        array $post = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $headers = [],
        string $method = 'GET',
        string $remoteAddress = '::1',
        ?string $rawBody = null
    ): self;

    /**
     * Initial request.
     *
     * @param array<string, string>|null $query
     * @param array<string, string>|null $post
     * @param array<string, string>|null $attributes
     * @param array<string, string>|null $cookies
     * @param array<string, string>|null $files
     * @param array<string, string>|null $headers
     * @return self
     */
    public function duplicate(
        ?array $query = null,
        ?array $post = null,
        ?array $attributes = null,
        ?array $cookies = null,
        ?array $files = null,
        ?array $headers = null
    ): self;

    /**
     * Retrieve the URL.
     *
     * @return string Return the URL.
     */
    public function getUrl(): string;

    /**
     * Retrieves query ($_GET).
     *
     * @return CollectionImmutable<string, string> Return the $_GET query.
     */
    public function query(): CollectionImmutable;

    /**
     * Retrieves Post/s ($_GET).
     *
     * @return array<string, string>|string Return the $_POST query.
     */
    public function getQuery(?string $key = null): array|string;

    /**
     * Retrieves post ($_POST).
     *
     * @return CollectionImmutable<string, string> Return the $_POST query.
     */
    public function post(): CollectionImmutable;

    /**
     * Get Post/s ($_POST).
     *
     * @return array<string, string>|string
     */
    public function getPost(?string $key = null): array|string;

    /**
     * Get file/s ($_FILE).
     *
     * @return array<string, array<int, string>|string>|array<int, string>|string
     */
    public function getFile(?string $key = null): array|string;

    /**
     * @param string $key
     * @return string|null
     */
    public function getCookie(string $key): ?string;

    /**
     * Get cookies.
     *
     * @return array<string, string>|null
     */
    public function getCookies(): ?array;

    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @param string $method
     * @return bool
     */
    public function isMethod(string $method): bool;

    /**
     * Get header/s.
     *
     * @param string|null $header
     * @return array<string, string>|string|null get header/s
     */
    public function getHeaders(?string $header = null): array|string|null;

    /**
     * Gets the mime types associated with the format.
     *
     * @param string $format
     * @return string[]
     */
    public function getMimeTypes(string $format): array;

    /**
     * Gets format using mimetype.
     *
     * @param string|null $mimeType
     * @return string|null
     */
    public function getFormat(?string $mimeType): ?string;

    /**
     * Gets format type from request header.
     *
     * @return string|null
     */
    public function getRequestFormat(): ?string;

    /**
     * @param string $headerKey
     * @param string $headerVal
     * @return bool
     */
    public function isHeader(string $headerKey, string $headerVal): bool;

    /**
     * @param string $headerKey
     * @return bool
     */
    public function hasHeader(string $headerKey): bool;

    /**
     * @return bool
     */
    public function isSecured(): bool;

    /**
     * @return string
     */
    public function getRemoteAddress(): string;

    /**
     * @return string|null
     */
    public function getRawBody(): ?string;

    /**
     * Get Json array.
     *
     * @return array
     * @throws Exception
     */
    public function getJsonBody(): array;

    /**
     * Get attribute.
     *
     * @param string $key
     * @param bool|int|string $default
     * @return string|int|bool
     */
    public function getAttribute(string $key, bool|int|string $default): bool|int|string;

    /**
     * Push costume attributes to the request,
     * uses for costume request to server.
     *
     * @param array<string, string|int|bool> $pushAttributes Push a attributes as array
     * @return self
     */
    public function with(array $pushAttributes): self;

    /**
     * Get all request as array.
     *
     * @return array<string, mixed> All request
     * @throws Exception
     */
    public function all(): array;

    /**
     * Get all request and wrap it.
     *
     * @return array<int, array<string, mixed>> Insert all request array in single array
     * @throws Exception
     */
    public function wrap(): array;

    /**
     * Determinate request is ajax.
     *
     * @return bool
     */
    public function isAjax(): bool;

    /**
     * Determinate request is json request.
     *
     * @return bool
     */
    public function isJson(): bool;

    /**
     * @return Collection<string, string>
     * @throws Exception
     */
    public function json(): Collection;

    /**
     * Combine all request input.
     *
     * @template TGetDefault
     *
     * @param TGetDefault $default
     *
     * @return Collection<string, string>|string|TGetDefault
     * @throws Exception
     */
    public function input(?string $key = null, $default = null);

    /**
     * Iterator.
     *
     * @return Traversable
     * @throws Exception
     */
    public function getIterator(): Traversable;
}
