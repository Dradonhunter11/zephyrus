<?php namespace Zephyrus\Network\Request;

use stdClass;
use Zephyrus\Network\ContentType;

class RequestBody
{
    private ?string $rawBody;

    public static function capture(): self
    {
        return new self(file_get_contents('php://input'));
    }

    public function __construct(?string $rawBody = null)
    {
        $this->rawBody = $rawBody;
    }

    public function parseJson(): stdClass
    {
        return json_decode($this->rawBody);
    }

    public function parseXml(): stdClass
    {
        return new \SimpleXMLElement($this->rawBody);
    }

    public function parse(ContentType $contentType): mixed
    {
        return match ($contentType) {
            ContentType::JSON => 1,
            ContentType::XML, ContentType::XML_APP => 2,
            default => 3
        };
    }


}
