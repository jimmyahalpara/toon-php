<?php

declare(strict_types=1);

namespace JimmyAhalpara\ToonFormat;

/**
 * Line writer for managing indented TOON output.
 *
 * Manages indented text generation with optimized indent string caching.
 */
class LineWriter
{
    /** @var array<int, string> */
    private array $lines = [];

    private string $indentationString;

    /** @var array<int, string> */
    private array $indentCache = [];

    private int $indentSize;

    public function __construct(int $indentSize)
    {
        $this->indentSize = $indentSize;

        // Ensure nested structures remain distinguishable even for indent=0
        $normalizedIndent = $indentSize > 0 ? $indentSize : 1;
        $this->indentationString = str_repeat(' ', $normalizedIndent);
        $this->indentCache[0] = '';
    }

    /**
     * Add a line with appropriate indentation.
     *
     * @param int $depth Indentation depth level
     * @param string $content Content to add
     */
    public function push(int $depth, string $content): void
    {
        // Use cached indent string for performance
        if (!isset($this->indentCache[$depth])) {
            if ($this->indentSize === 0) {
                // indent=0 uses minimal spacing to preserve structure
                $this->indentCache[$depth] = str_repeat(' ', $depth);
            } else {
                $this->indentCache[$depth] = str_repeat($this->indentationString, $depth);
            }
        }

        $indent = $this->indentCache[$depth];
        $this->lines[] = $indent . $content;
    }

    /**
     * Return all lines joined with newlines.
     */
    public function toString(): string
    {
        return implode("\n", $this->lines);
    }

    /**
     * Get the number of lines.
     */
    public function count(): int
    {
        return count($this->lines);
    }

    /**
     * Clear all lines.
     */
    public function clear(): void
    {
        $this->lines = [];
    }
}
