<?php

declare(strict_types=1);

namespace Pobo\Sdk\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Pobo\Sdk\Exception\ApiException;

final class ApiExceptionTest extends TestCase
{
    public function testUnauthorized(): void
    {
        $exception = ApiException::unauthorized();

        $this->assertSame('Authorization token required or invalid', $exception->getMessage());
        $this->assertSame(401, $exception->httpCode);
        $this->assertNull($exception->responseBody);
    }

    public function testFromResponseWithMessage(): void
    {
        $body = ['message' => 'Not Found'];
        $exception = ApiException::fromResponse(404, $body);

        $this->assertSame('Not Found', $exception->getMessage());
        $this->assertSame(404, $exception->httpCode);
        $this->assertSame($body, $exception->responseBody);
    }

    public function testFromResponseWithError(): void
    {
        $body = ['error' => 'Server Error'];
        $exception = ApiException::fromResponse(500, $body);

        $this->assertSame('Server Error', $exception->getMessage());
        $this->assertSame(500, $exception->httpCode);
    }

    public function testFromResponseWithNullBody(): void
    {
        $exception = ApiException::fromResponse(502, null);

        $this->assertSame('API request failed with HTTP 502', $exception->getMessage());
        $this->assertSame(502, $exception->httpCode);
        $this->assertNull($exception->responseBody);
    }

    public function testFromResponseWithEmptyBody(): void
    {
        $exception = ApiException::fromResponse(500, []);

        $this->assertSame('API request failed with HTTP 500', $exception->getMessage());
        $this->assertSame(500, $exception->httpCode);
    }

    public function testFromResponsePrefersMessageOverError(): void
    {
        $body = ['message' => 'Detailed message', 'error' => 'Generic error'];
        $exception = ApiException::fromResponse(422, $body);

        $this->assertSame('Detailed message', $exception->getMessage());
    }

    public function testConstructorStoresAllProperties(): void
    {
        $body = ['detail' => 'some info'];
        $exception = new ApiException('Custom error', 418, $body);

        $this->assertSame('Custom error', $exception->getMessage());
        $this->assertSame(418, $exception->httpCode);
        $this->assertSame(418, $exception->getCode());
        $this->assertSame($body, $exception->responseBody);
    }
}
