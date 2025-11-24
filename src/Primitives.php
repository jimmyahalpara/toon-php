<?php

declare(strict_types=1);

namespace JimmyAhalpara\ToonFormat;

/**
 * Primitive value encoding utilities.
 *
 * Handles encoding of primitive values (strings, numbers, booleans, null) and
 * array headers. Implements quoting rules and header formatting.
 */
class Primitives
{
    /**
     * Encode a primitive value.
     *
     * @param mixed $value Primitive value
     * @param string $delimiter Current delimiter being used
     */
    public static function encodePrimitive(mixed $value, string $delimiter = Constants::COMMA): string
    {
        if ($value === null) {
            return Constants::NULL_LITERAL;
        }

        if (is_bool($value)) {
            return $value ? Constants::TRUE_LITERAL : Constants::FALSE_LITERAL;
        }

        if (is_int($value)) {
            return (string)$value;
        }

        if (is_float($value)) {
            // Format numbers in decimal form without scientific notation
            $formatted = (string)$value;

            // Check if PHP used scientific notation
            if (str_contains($formatted, 'e') || str_contains($formatted, 'E')) {
                // Convert to fixed-point decimal notation
                $formatted = rtrim(sprintf('%.14F', $value), '0');
                $formatted = rtrim($formatted, '.');
            }

            return $formatted;
        }

        if (is_string($value)) {
            return self::encodeStringLiteral($value, $delimiter);
        }

        return (string)$value;
    }

    /**
     * Encode a string, quoting only if necessary.
     *
     * @param string $value String value
     * @param string $delimiter Current delimiter being used
     */
    public static function encodeStringLiteral(string $value, string $delimiter = Constants::COMMA): string
    {
        if (StringUtils::isSafeUnquoted($value, $delimiter)) {
            return $value;
        }

        return Constants::DOUBLE_QUOTE . StringUtils::escapeString($value) . Constants::DOUBLE_QUOTE;
    }

    /**
     * Encode an object key.
     *
     * @param string $key Key string
     */
    public static function encodeKey(string $key): string
    {
        if (StringUtils::isValidUnquotedKey($key)) {
            return $key;
        }

        return Constants::DOUBLE_QUOTE . StringUtils::escapeString($key) . Constants::DOUBLE_QUOTE;
    }

    /**
     * Join encoded primitive values with a delimiter.
     *
     * @param array<string> $values List of encoded values
     * @param string $delimiter Delimiter to use
     */
    public static function joinEncodedValues(array $values, string $delimiter): string
    {
        return implode($delimiter, $values);
    }

    /**
     * Format array/table header.
     *
     * @param string|null $key Optional key name
     * @param int $length Array length
     * @param array<string>|null $fields Optional field names for tabular format
     * @param string $delimiter Delimiter character
     * @param string|false $lengthMarker Optional length marker prefix
     */
    public static function formatHeader(
        ?string $key,
        int $length,
        ?array $fields,
        string $delimiter,
        string|false $lengthMarker
    ): string {
        // Build length marker
        $markerPrefix = $lengthMarker !== false ? $lengthMarker : '';

        // Build fields if provided
        $fieldsStr = '';
        if ($fields !== null) {
            $encodedFields = array_map([self::class, 'encodeKey'], $fields);
            $fieldsStr = Constants::OPEN_BRACE . implode($delimiter, $encodedFields) . Constants::CLOSE_BRACE;
        }

        // Build length string with delimiter when needed
        // Rules: delimiter is optional in bracket [N<delim?>]
        // Only include delimiter if it's NOT comma (comma is the default)
        if ($delimiter !== Constants::COMMA) {
            $lengthStr = Constants::OPEN_BRACKET . $markerPrefix . $length . $delimiter . Constants::CLOSE_BRACKET;
        } else {
            $lengthStr = Constants::OPEN_BRACKET . $markerPrefix . $length . Constants::CLOSE_BRACKET;
        }

        // Combine parts
        if ($key !== null) {
            return self::encodeKey($key) . $lengthStr . $fieldsStr . Constants::COLON;
        }

        return $lengthStr . $fieldsStr . Constants::COLON;
    }
}
