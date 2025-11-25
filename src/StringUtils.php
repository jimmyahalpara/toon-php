<?php

declare(strict_types=1);

namespace JimmyAhalpara\ToonFormat;

use JimmyAhalpara\ToonFormat\Exception\ToonDecodeException;

/**
 * String escaping and unescaping utilities.
 */
class StringUtils
{
    /**
     * Escape a string for use in TOON format.
     *
     * @param string $value
     * @return string Escaped string (without surrounding quotes)
     */
    public static function escapeString(string $value): string
    {
        $result = '';
        $length = strlen($value);

        for ($i = 0; $i < $length; $i++) {
            $char = $value[$i];
            $ord = ord($char);

            // Handle special escape sequences
            switch ($char) {
                case '"':
                    $result .= '\\"';

                    break;
                case '\\':
                    $result .= '\\\\';

                    break;
                case "\n":
                    $result .= '\\n';

                    break;
                case "\r":
                    $result .= '\\r';

                    break;
                case "\t":
                    $result .= '\\t';

                    break;
                default:
                    // Handle control characters
                    if ($ord < 32 || $ord === 127) {
                        $result .= sprintf('\\u%04x', $ord);
                    } else {
                        $result .= $char;
                    }
            }
        }

        return $result;
    }

    /**
     * Unescape a string from TOON format.
     *
     * @param string $value Escaped string (without surrounding quotes)
     * @return string Unescaped string
     * @throws ToonDecodeException If escape sequence is invalid
     */
    public static function unescapeString(string $value): string
    {
        $result = '';
        $length = strlen($value);
        $i = 0;

        while ($i < $length) {
            $char = $value[$i];

            if ($char === '\\') {
                if ($i + 1 >= $length) {
                    throw new ToonDecodeException('Incomplete escape sequence at end of string');
                }

                $nextChar = $value[$i + 1];

                switch ($nextChar) {
                    case '"':
                        $result .= '"';
                        $i += 2;

                        break;
                    case '\\':
                        $result .= '\\';
                        $i += 2;

                        break;
                    case 'n':
                        $result .= "\n";
                        $i += 2;

                        break;
                    case 'r':
                        $result .= "\r";
                        $i += 2;

                        break;
                    case 't':
                        $result .= "\t";
                        $i += 2;

                        break;
                    case 'u':
                        // Unicode escape: \uXXXX
                        if ($i + 5 >= $length) {
                            throw new ToonDecodeException('Incomplete unicode escape sequence');
                        }

                        $hexCode = substr($value, $i + 2, 4);
                        if (! ctype_xdigit($hexCode)) {
                            throw new ToonDecodeException("Invalid unicode escape sequence: \\u{$hexCode}");
                        }

                        $codePoint = (int) hexdec($hexCode);
                        $result .= mb_chr($codePoint, 'UTF-8');
                        $i += 6;

                        break;
                    default:
                        throw new ToonDecodeException("Invalid escape sequence: \\{$nextChar}");
                }
            } else {
                $result .= $char;
                $i++;
            }
        }

        return $result;
    }

    /**
     * Check if a string is safe to use unquoted.
     *
     * @param string $value
     * @param string $delimiter Current delimiter being used
     */
    public static function isSafeUnquoted(string $value, string $delimiter): bool
    {
        // Empty string requires quotes
        if ($value === '') {
            return false;
        }

        // Reserved keywords require quotes
        if (in_array($value, [Constants::NULL_LITERAL, Constants::TRUE_LITERAL, Constants::FALSE_LITERAL], true)) {
            return false;
        }

        // Numeric-looking strings require quotes
        if (preg_match(Constants::NUMERIC_REGEX, $value)) {
            return false;
        }

        // Octal-like strings require quotes (e.g., "0123")
        if (preg_match(Constants::OCTAL_REGEX, $value)) {
            return false;
        }

        // Leading or trailing whitespace requires quotes
        if (trim($value) !== $value) {
            return false;
        }

        // Check for structural characters
        if (preg_match(Constants::STRUCTURAL_CHARS_REGEX, $value)) {
            return false;
        }

        // Check for control characters
        if (preg_match(Constants::CONTROL_CHARS_REGEX, $value)) {
            return false;
        }

        // Check for delimiter character
        if (str_contains($value, $delimiter)) {
            return false;
        }

        return true;
    }

    /**
     * Check if a key is valid as an unquoted identifier.
     *
     * @param string $key
     */
    public static function isValidUnquotedKey(string $key): bool
    {
        return preg_match(Constants::VALID_KEY_REGEX, $key) === 1;
    }
}
