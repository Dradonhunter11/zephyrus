<?php namespace Zephyrus\Network;

class Request
{
    /**
     * @var mixed[] every parameters included in the request
     */
    private $parameters = [];

    /**
     * @var string HTTP method used by client
     */
    private $method;

    /**
     * @var string ip address of originated request
     */
    private $clientIp;

    /**
     * @var string accepted representation from the client
     */
    private $accept;

    /**
     * @var Uri destined uri of the request
     */
    private $uri;

    /**
     * @var string requested uri as it was received (e.g /users/1)
     */
    private $requestedUri;

    /**
     * @var string destined full url of request
     */
    private $baseUrl;

    /**
     * @var string specified user agent (e.g Chrome)
     */
    private $userAgent;

    /**
     * @var mixed[] list of all server variables ($_SERVER)
     */
    private $serverVariables;

    /**
     * @var mixed[] list of all specified cookies
     */
    private $cookies;

    /**
     * @var mixed[] list of all uploaded files
     */
    private $files;

    /**
     * Request constructor which need the option array data to populate the
     * request. E.g.
     *
     * 'parameters' => ['t' => 1, 'z' => 5],
     * 'cookies' => $_COOKIE,
     * 'files' => $_FILES,
     * 'server' => $_SERVER
     *
     * @param string $uri
     * @param string $method
     * @param array $options
     */
    public function __construct(string $uri, string $method, array $options = [])
    {
        $this->uri = new Uri($uri);
        $this->requestedUri = $uri;
        $this->method = $method;
        $this->parameters = $options['parameters'] ?? [];
        $this->serverVariables = $options['server'] ?? [];
        $this->cookies = $options['cookies'] ?? [];
        $this->files = $options['files'] ?? [];
        $this->initializeServer();
        $this->initializeBaseUrl();
    }

    /**
     * @param string $name
     * @param string $defaultValue
     * @return string
     */
    public function getCookieValue(string $name, ?string $defaultValue = null): ?string
    {
        return (isset($this->cookies[$name])) ? $this->cookies[$name] : $defaultValue;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasCookie(string $name): bool
    {
        return isset($this->cookies[$name]);
    }

    /**
     * @return mixed[]
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * @param $name
     * @return mixed[]
     */
    public function getFile($name)
    {
        if (isset($this->files[$name])) {
            return $this->files[$name];
        }
        return null;
    }

    /**
     * @return mixed[]
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @return string
     */
    public function getClientIp()
    {
        return $this->clientIp;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function addParameter($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function prependParameter($name, $value)
    {
        $this->parameters = array_merge([$name => $value], $this->parameters);
    }

    /**
     * @return mixed[]
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    public function getParameter(string $name, $default = null)
    {
        if (isset($this->parameters[$name])) {
            return $this->parameters[$name];
        }
        return $default;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getAccept()
    {
        return $this->accept;
    }

    /**
     * @return Uri
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @return string
     */
    public function getRequestedUri(): string
    {
        return $this->requestedUri;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return (function_exists('getallheaders')) ? getallheaders() : [];
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function getHeader(string $name): ?string
    {
        $headers = $this->getHeaders();
        return isset($headers[$name]) ? $headers[$name] : null;
    }

    private function initializeServer()
    {
        $this->accept = $this->serverVariables['HTTP_ACCEPT'] ?? '';
        $this->userAgent = $this->serverVariables['HTTP_USER_AGENT'] ?? '';
        $this->clientIp = $this->serverVariables['REMOTE_ADDR'] ?? '';
    }

    private function initializeBaseUrl()
    {
        $this->baseUrl = ($this->uri->isSecure()) ? 'https://' : 'http://';
        $this->baseUrl .= $this->uri->getHost();
    }
}
