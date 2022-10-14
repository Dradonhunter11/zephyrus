<?php namespace Zephyrus\Network\Request;

class RequestHeader
{
    private array $headers;

    public function __construct(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * Retrieves the defined accepted representations order by specified priority using the standard parameter "q" which
     * should range from 0 (lowest) to 1 (highest).
     *
     * @return array
     */
    public function getAcceptedRepresentations(): array
    {
        $acceptedRepresentations = explode(',', $this->accept);
        array_walk($acceptedRepresentations, function (&$accept) {
            // When no priority parameter is given, use natural defined order
            // by adding q=1.
            if (strpos($accept, ';q') === false) {
                $accept .= ';q=1';
            }
            $accept = explode(';q=', $accept);
        });
        usort($acceptedRepresentations, function ($a, $b) {
            // Sort using priority parameters
            return $b[1] <=> $a[1];
        });
        usort($acceptedRepresentations, function ($a, $b) {
            // Sort using specificity (*/*) for same priority
            if ($a[1] == $b[1]) {
                return substr_count($a[0], '*') <=> substr_count($b[0], '*');
            }
            return 0;
        });
        return array_filter(array_column($acceptedRepresentations, 0));
    }




    public function get(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    public function getContentType(): ?string
    {
        return $this->get('Content-Type');
    }

    public function getContentLength(): int
    {
        return (int) $this->get('Content-Length');
    }

    public function getAccept(): ?string
    {
        return $this->get('Accept');
    }

    public function getAcceptLanguage(): ?string
    {
        return $this->get('Accept-Language');
    }

    public function getAcceptEncoding(): ?string
    {
        return $this->get('Accept-Encoding');
    }

    public function getAcceptCharset(): ?string
    {
        return $this->get('Accept-Charset');
    }

    public function getAuthorization(): ?string
    {
        return $this->get('Authorization');
    }

    public function getHost(): ?string
    {
        return $this->get('Host');
    }

    public function getOrigin(): ?string
    {
        return $this->get('Origin');
    }

    public function getReferer(): ?string
    {
        return $this->get('Referer');
    }

    public function getUserAgent(): ?string
    {
        return $this->get('User-Agent');
    }

    public function getIfModifiedSince(): ?string
    {
        return $this->get('If-Modified-Since');
    }
}
