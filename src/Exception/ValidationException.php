<?php

declare(strict_types=1);

namespace Pobo\Sdk\Exception;

class ValidationException extends PoboException
{
    /**
     * @param array<string, array<string>> $errors
     */
    public function __construct(
        string $message,
        public readonly array $errors = [],
    ) {
        parent::__construct($message);
    }

    public static function tooManyItems(int $count, int $max): self
    {
        return new self(
            sprintf('Too many items: %d provided, maximum is %d', $count, $max),
            ['bulk' => [sprintf('Maximum %d items allowed for bulk import', $max)]]
        );
    }

    public static function emptyPayload(): self
    {
        return new self('Payload cannot be empty', ['bulk' => ['At least one item is required']]);
    }
}
