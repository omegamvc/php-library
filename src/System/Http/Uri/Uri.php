<?php


declare(strict_types=1);

namespace System\Http\Uri;

use System\Http\Request\Request;

use function array_key_exists;
use function parse_str;
use function parse_url;

class Uri implements UriInterface
{
    /** @var string|int|string[]|null  */
    private string|int|array|null $schema;

    /** @var string|int|string[]|null  */
    private string|int|array|null $host;

    /** @var int|string|string[]|null  */
    private int|string|array|null $port;

    /** @var string|int|string[]|null  */
    private string|int|array|null $user;

    /** @var string|int|string[]|null  */
    private string|int|array|null $password;

    /** @var string|int|string[]|null  */
    private string|int|array|null $path;

    /** @var array<int|string, string>|null */
    private ?array $query = null;

    /** @var string|int|string[]|null  */
    private string|int|array|null $fragment;

    /**
     * @param array<string, string|int|array<int|string, string>|null> $parseUrl
     */
    public function __construct(array $parseUrl)
    {
        $this->schema    = $parseUrl['scheme'] ?? null;
        $this->host      = $parseUrl['host'] ?? null;
        $this->port      = $parseUrl['port'] ?? null;
        $this->user      = $parseUrl['user'] ?? null;
        $this->password  = $parseUrl['pass'] ?? null;
        $this->path      = $parseUrl['path'] ?? null;
        $this->fragment  = $parseUrl['fragment'] ?? null;

        if (array_key_exists('query', $parseUrl)) {
            $this->query = $this->parseQuery($parseUrl['query']);
        }
    }

    /**
     * @param string $query
     * @return array<int|string, string>
     */
    private function parseQuery(string $query): array
    {
        $result = [];
        parse_str($query, $result);

        return $result;
    }

    /**
     * @param string $url
     * @return self
     */
    public static function parse(string $url): self
    {
        return new self(parse_url($url));
    }

    /**
     * @param Request $from
     * @return self
     */
    public static function fromRequest(Request $from): self
    {
        return new self(parse_url($from->getUrl()));
    }

    /**
     * @return string|null
     */
    public function schema(): ?string
    {
        return $this->schema;
    }

    /**
     * @return string|null
     */
    public function host(): ?string
    {
        return $this->host;
    }

    /**
     * @return int|null
     */
    public function port(): ?int
    {
        return $this->port;
    }

    /**
     * @return string|null
     */
    public function user(): ?string
    {
        return $this->user;
    }

    /**
     * @return string|null
     */
    public function password(): ?string
    {
        return $this->password;
    }

    /**
     * @return string|null
     */
    public function path(): ?string
    {
        return $this->path;
    }

    /**
     * @return array<int|string, string>|null
     */
    public function query(): ?array
    {
        return $this->query;
    }

    /**
     * @return string|null
     */
    public function fragment(): ?string
    {
        return $this->fragment;
    }

    /**
     * @return bool
     */
    public function hasSchema(): bool
    {
        return null !== $this->schema;
    }

    /**
     * @return bool
     */
    public function hasHost(): bool
    {
        return null !== $this->host;
    }

    /**
     * @return bool
     */
    public function hasPort(): bool
    {
        return null !== $this->port;
    }

    /**
     * @return bool
     */
    public function hasUser(): bool
    {
        return null !== $this->user;
    }

    /**
     * @return bool
     */
    public function hasPassword(): bool
    {
        return null !== $this->password;
    }

    /**
     * @return bool
     */
    public function hasPath(): bool
    {
        return null !== $this->path;
    }

    /**
     * @return bool
     */
    public function hasQuery(): bool
    {
        return null !== $this->query;
    }

    /**
     * @return bool
     */
    public function hasFragment(): bool
    {
        return null !== $this->fragment;
    }
}
