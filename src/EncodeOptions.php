<?php

declare(strict_types=1);

namespace JimmyAhalpara\ToonFormat;

/**
 * Options for TOON encoding.
 */
class EncodeOptions
{
    private int $indent;
    private string $delimiter;
    private string|false $lengthMarker;

    /**
     * @param int $indent Number of spaces per indentation level (default: 2)
     * @param string $delimiter Delimiter character for arrays (default: comma)
     * @param string|false $lengthMarker Optional marker to prefix array lengths (default: false)
     */
    public function __construct(
        int $indent = Constants::DEFAULT_INDENT,
        string $delimiter = Constants::DEFAULT_DELIMITER,
        string|false $lengthMarker = false
    ) {
        $this->indent = $indent;
        $this->delimiter = $delimiter;
        $this->lengthMarker = $lengthMarker;
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
            $options['delimiter'] ?? Constants::DEFAULT_DELIMITER,
            $options['lengthMarker'] ?? false
        );
    }

    public function getIndent(): int
    {
        return $this->indent;
    }

    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    public function getLengthMarker(): string|false
    {
        return $this->lengthMarker;
    }

    public function setIndent(int $indent): void
    {
        $this->indent = $indent;
    }

    public function setDelimiter(string $delimiter): void
    {
        $this->delimiter = $delimiter;
    }

    public function setLengthMarker(string|false $lengthMarker): void
    {
        $this->lengthMarker = $lengthMarker;
    }
}
