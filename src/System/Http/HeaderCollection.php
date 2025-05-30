<?php

declare(strict_types=1);

namespace System\Http;

use Exception;
use System\Collection\Collection;
use System\Text\Str;

use function array_map;
use function explode;
use function implode;
use function is_array;
use function is_int;
use function preg_split;
use function rtrim;
use function str_contains;
use function str_ends_with;
use function str_starts_with;
use function substr;
use function trim;

/**
 * @extends Collection<string, string>
 */
class HeaderCollection extends Collection
{
    /**
     * Header collection.
     *
     * @param array<string, string> $headers
     */
    public function __construct($headers)
    {
        parent::__construct($headers);
    }

    public function __toString()
    {
        $headers = $this->clone()->map(fn (string $value, string $key = ''): string => "{$key}: {$value}")->toArray();

        return implode("\r\n", $headers);
    }

    /**
     * Set raw header.
     * @param string $header
     * @return $this
     * @throws Exception
     */
    public function setRaw(string $header): self
    {
        if (false === Str::contains($header, ':')) {
            throw new Exception("Invalid header structure {$header}.");
        }

        [$header_name, $header_val] = explode(':', $header, 2);

        return $this->set(trim($header_name), trim($header_val));
    }

    /**
     * Get header directly.
     *
     * @param string $header
     * @return array<string|int, string|string[]>
     */
    public function getDirective(string $header): array
    {
        return $this->parseDirective($header);
    }

    /**
     * Add new header value directly to exist header.
     *
     * @param string                             $header
     * @param array<int|string, string|string[]> $value
     * @return $this
     */
    public function addDirective(string $header, array $value): self
    {
        $items = $this->parseDirective($header);
        foreach ($value as $key => $new_item) {
            if (is_int($key)) {
                $items[] = $new_item;
                continue;
            }
            $items[$key] = $new_item;
        }

        return $this->set($header, $this->encodeToString($items));
    }

    /**
     * Remove exits header directly.
     *
     * @param string $header
     * @param string $item
     * @return self
     */
    public function removeDirective(string $header, string $item): self
    {
        $items    = $this->parseDirective($header);
        $newItems = [];
        foreach ($items as $key => $value) {
            if ($key === $item) {
                continue;
            }
            if ($value === $item) {
                continue;
            }
            $newItems[$key] = $value;
        }

        return $this->set($header, $this->encodeToString($newItems));
    }

    /**
     * Check header directive has item/key.
     *
     * @param string $header
     * @param string $item
     * @return bool
     */
    public function hasDirective(string $header, string $item): bool
    {
        $items = $this->parseDirective($header);
        foreach ($items as $key => $value) {
            if ($key === $item) {
                return true;
            }
            if ($value === $item) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse header item to array.
     *
     * @param string $key
     * @return array<string|int, string|string[]>
     */
    private function parseDirective(string $key): array
    {
        if (false === $this->has($key)) {
            return [];
        }

        $header        = $this->get($key);
        $pattern       = '/,\s*(?=(?:[^\"]*\"[^\"]*\")*[^\"]*$)/';
        $header_item   = preg_split($pattern, $header);

        $result = [];
        foreach ($header_item as $item) {
            if (str_contains($item, '=')) {
                $parts = explode('=', $item, 2);
                $key   = trim($parts[0]);
                $value = trim($parts[1]);
                if (str_starts_with($value, '"') && str_ends_with($value, '"')) {
                    $value = substr($value, 1, -1);
                    $value = array_map('trim', explode(', ', $value));
                }
                $result[$key] = $value;
                continue;
            }
            $result[] = $item;
        }

        return $result;
    }

    /**
     * Encode array data to header string.
     *
     * @param array<string|int, string|string[]> $data
     * @return string
     */
    private function encodeToString(array $data): string
    {
        $encodedString = '';

        foreach ($data as $key => $value) {
            if (is_int($key)) {
                $encodedString .= $value . ', ';
                continue;
            }

            if (is_array($value)) {
                $value = '"' . implode(', ', $value) . '"';
            }
            $encodedString .= $key . '=' . $value . ', ';
        }

        return rtrim($encodedString, ', ');
    }
}
