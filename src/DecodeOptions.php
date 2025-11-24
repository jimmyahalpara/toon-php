<?php

declare(strict_types=1);

namespace JimmyAhalpara\ToonFormat;

/**
 * Options for TOON decoding.
 */
class DecodeOptions
{
    private int $indent;
    private bool $strict;

    /**
     * @param int $indent Expected spaces per indentation level (default: 2)
     * @param bool $strict Enable strict validation (default: true)
     */
    public function __construct(
        int $indent = Constants::DEFAULT_INDENT,
        bool $strict = true
    ) {
        $this->indent = $indent;
        $this->strict = $strict;
    }

    /**
     * Create from array of options.
     *
     * @param array<string, mixed> $options
     */
    public static function fromArray(array $options): self
    {
        return new self(
            $options['indent'] ?? Constants::DEFAULT_INDENT,
            $options['strict'] ?? true
        );
    }

    public function getIndent(): int
    {
        return $this->indent;
    }

    public function isStrict(): bool
    {
        return $this->strict;
    }

    public function setIndent(int $indent): void
    {
        $this->indent = $indent;
    }

    public function setStrict(bool $strict): void
    {
        $this->strict = $strict;
    }
}
