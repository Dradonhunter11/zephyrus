<?php namespace Zephyrus\Network\Request;

class RequestClient
{
    public function __construct(array $requestHeaders)
    {

    }

    public function getIpAddress(): string
    {

    }

    public function getUserAgent(): ?string
    {

    }

    public function getBrowser(): ?string
    {

    }

    public function getOperatingSystem(): ?string
    {

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
}
