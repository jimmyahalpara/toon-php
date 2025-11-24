<?php

declare(strict_types=1);

namespace JimmyAhalpara\ToonFormat\Exception;

use Exception;

/**
 * Exception thrown when TOON decoding fails.
 */
class ToonDecodeException extends Exception
{
    private ?int $lineNumber = null;

    public function __construct(string $message, ?int $lineNumber = null)
    {
        $this->lineNumber = $lineNumber;

        if ($lineNumber !== null) {
            $message = sprintf('Line %d: %s', $lineNumber, $message);
        }

        parent::__construct($message);
    }

    public function getLineNumber(): ?int
    {
        return $this->lineNumber;
    }
}
