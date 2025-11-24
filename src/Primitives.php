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
        $markerPrefix = ($lengthMarker !== false) ? $lengthMarker : '';

        // Build fields if provided
        $fieldsStr = '';
        if ($fields !== null) {
            $encodedFields = [];
            foreach ($fields as $field) {
                $encodedFields[] = self::encodeKey($field);
            }
            $openBrace = chr(123); // {
            $closeBrace = chr(125); // }
            $fieldsStr = $openBrace . implode($delimiter, $encodedFields) . $closeBrace;
        }

        // Build length string with delimiter when needed
        // Rules: delimiter must be shown in bracket when fields are present (tabular format)
        // For primitive arrays, delimiter is optional (only shown for non-comma delimiters)
        if ($fields !== null) {
            // Tabular format: always show delimiter
            $lengthStr = '[' . $markerPrefix . $length . $delimiter . ']';
        } elseif ($delimiter !== Constants::COMMA) {
            // Primitive array with non-comma delimiter
            $lengthStr = '[' . $markerPrefix . $length . $delimiter . ']';
        } else {
            // Primitive array with comma delimiter (default)
            $lengthStr = '[' . $markerPrefix . $length . ']';
        }

        // Combine parts
        if ($key !== null) {
            return self::encodeKey($key) . $lengthStr . $fieldsStr . ':';
        }

        return $lengthStr . $fieldsStr . ':';
    }
}

