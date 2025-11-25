<?php

declare(strict_types=1);

namespace JimmyAhalpara\ToonFormat;

use DateTime;
use DateTimeInterface;
use stdClass;

/**
 * Value normalization for TOON encoding.
 *
 * Converts PHP-specific types to JSON-compatible values:
 * - DateTime → ISO 8601 strings
 * - stdClass → associative arrays
 * - INF/NAN → null
 * - Negative zero → positive zero
 */
class Normalizer
{
    /**
     * Normalize PHP value to JSON-compatible type.
     *
     * @param mixed $value
     * @return mixed Normalized value (null, bool, int, float, string, array)
     */
    public static function normalizeValue(mixed $value): mixed
    {
        // Handle null
        if ($value === null) {
            return null;
        }

        // Handle booleans
        if (is_bool($value)) {
            return $value;
        }

        // Handle strings
        if (is_string($value)) {
            return $value;
        }

        // Handle integers
        if (is_int($value)) {
            return $value;
        }

        // Handle floats
        if (is_float($value)) {
            // Handle non-finite values
            if (! is_finite($value) || is_nan($value)) {
                return null;
            }

            // Handle negative zero
            // @phpstan-ignore-next-line Division by zero is intentional to detect -0.0
            if ($value === 0.0 && 1 / $value === -INF) {
                return 0;
            }

            return $value;
        }

        // Handle DateTime objects
        if ($value instanceof DateTimeInterface) {
            return $value->format('c'); // ISO 8601 format
        }

        // Handle arrays (both sequential and associative)
        if (is_array($value)) {
            if (empty($value)) {
                return [];
            }

            // Check if it's a sequential array (list)
            if (array_keys($value) === range(0, count($value) - 1)) {
                return array_map([self::class, 'normalizeValue'], $value);
            }

            // Associative array - normalize to object-like structure
            $normalized = [];
            foreach ($value as $key => $val) {
                $normalized[(string)$key] = self::normalizeValue($val);
            }

            return $normalized;
        }

        // Handle stdClass objects
        if ($value instanceof stdClass) {
            $vars = get_object_vars($value);

            // Keep empty stdClass as is to distinguish from empty array
            if (empty($vars)) {
                return $value;
            }

            $normalized = [];
            foreach ($vars as $key => $val) {
                $normalized[$key] = self::normalizeValue($val);
            }

            return $normalized;
        }

        // Handle other objects with toArray method
        if (is_object($value) && method_exists($value, 'toArray')) {
            return self::normalizeValue($value->toArray());
        }

        // Handle other objects with jsonSerialize method
        if ($value instanceof \JsonSerializable) {
            return self::normalizeValue($value->jsonSerialize());
        }

        // Handle callables
        if (is_callable($value)) {
            return null;
        }

        // Handle resources
        if (is_resource($value)) {
            return null;
        }

        // Fallback for other types
        return null;
    }

    /**
     * Check if value is a JSON primitive type.
     *
     * @param mixed $value
     */
    public static function isJsonPrimitive(mixed $value): bool
    {
        return $value === null
            || is_string($value)
            || is_int($value)
            || is_float($value)
            || is_bool($value);
    }

    /**
     * Check if value is a JSON array (PHP list).
     *
     * @param mixed $value
     */
    public static function isJsonArray(mixed $value): bool
    {
        if (! is_array($value)) {
            return false;
        }

        if (empty($value)) {
            return true;
        }

        // Check if it's a sequential array
        return array_keys($value) === range(0, count($value) - 1);
    }

    /**
     * Check if value is a JSON object (PHP associative array).
     *
     * @param mixed $value
     */
    public static function isJsonObject(mixed $value): bool
    {
        if (! is_array($value)) {
            return false;
        }

        if (empty($value)) {
            return false; // Empty arrays are arrays, not objects
        }

        // Check if it's NOT a sequential array
        return array_keys($value) !== range(0, count($value) - 1);
    }

    /**
     * Check if array contains only primitive values.
     *
     * @param array<mixed> $value
     */
    public static function isArrayOfPrimitives(array $value): bool
    {
        if (empty($value)) {
            return true;
        }

        foreach ($value as $item) {
            if (! self::isJsonPrimitive($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if array contains only arrays.
     *
     * @param array<mixed> $value
     */
    public static function isArrayOfArrays(array $value): bool
    {
        if (empty($value)) {
            return true;
        }

        foreach ($value as $item) {
            if (! self::isJsonArray($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if array contains only objects (associative arrays).
     *
     * @param array<mixed> $value
     */
    public static function isArrayOfObjects(array $value): bool
    {
        if (empty($value)) {
            return true;
        }

        foreach ($value as $item) {
            if (! self::isJsonObject($item)) {
                return false;
            }
        }

        return true;
    }
}
