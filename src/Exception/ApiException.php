<?php

declare(strict_types=1);

namespace Pobo\Sdk\Exception;

class ApiException extends PoboException
{
    public function __construct(
        string $message,
        public readonly int $httpCode,
        public readonly ?array $responseBody = null,
    ) {
        parent::__construct($message, $httpCode);
    }

    public static function unauthorized(): self
    {
        return new self('Authorization token required or invalid', 401);
    }

    public static function fromResponse(int $httpCode, ?array $body): self
    {
        $message = $body['message'] ?? $body['error'] ?? sprintf('API request failed with HTTP %d', $httpCode);
        return new self($message, $httpCode, $body);
    }
}
