<?php namespace Zephyrus\Network\Request;

use function Zephyrus\Network\setcookie;

class CookieJar
{
    private array $cookies;

    public static function capture(): self
    {
        return new self($_COOKIE);
    }

    public function __construct(array $cookies)
    {
        $this->cookies = $cookies;
    }

    public function get(string $name): ?string
    {
        return $this->cookies[$name] ?? null;
    }

    public function has(string $name): bool
    {
        return isset($this->cookies[$name]);
    }

    public function set(string $name, string $value, int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = false): void
    {
        setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }

    public function delete(string $name): void
    {
        setcookie($name, '', time() - 3600);
    }
}