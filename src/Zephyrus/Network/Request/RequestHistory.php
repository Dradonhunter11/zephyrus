<?php namespace Zephyrus\Network\Request;

class RequestHistory
{
    private const SESSION_KEY = '__HISTORY';
    private array $history;

    public function __construct()
    {
        $this->history = $_SESSION[self::SESSION_KEY] ?? [];
    }

    public function keep(string $url): void
    {
        array_unshift($this->history, $url);
        $_SESSION[self::SESSION_KEY] = $this->history;
    }

    public function getReferer(): string
    {
        return $this->history[0] ?? '/';
    }


}