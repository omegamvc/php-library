<?php

namespace System\Http\Uri;

interface UriInterface
{
    /**
     * @return string|null
     */
    public function schema(): ?string;

    /**
     * @return string|null
     */
    public function host(): ?string;

    /**
     * @return int|null
     */
    public function port(): ?int;

    /**
     * @return string|null
     */
    public function user(): ?string;

    /**
     * @return string|null
     */
    public function password(): ?string;

    /**
     * @return string|null
     */
    public function path(): ?string;

    /**
     * @return array<int|string, string>|null
     */
    public function query(): ?array;

    /**
     * @return string|null
     */
    public function fragment(): ?string;

    public function hasSchema(): bool;

    public function hasHost(): bool;

    public function hasPort(): bool;

    public function hasUser(): bool;

    public function hasPassword(): bool;

    public function hasPath(): bool;

    public function hasQuery(): bool;

    public function hasFragment(): bool;
}
