<?php

declare(strict_types=1);

namespace Pobo\Sdk\Exception;

class WebhookException extends PoboException
{
    public static function invalidSignature(): self
    {
        return new self('Invalid webhook signature');
    }

    public static function invalidPayload(): self
    {
        return new self('Invalid webhook payload - could not parse JSON');
    }

    public static function missingSignature(): self
    {
        return new self('Missing webhook signature header');
    }

    public static function unknownEvent(string $event): self
    {
        return new self(sprintf('Unknown webhook event: %s', $event));
    }
}
