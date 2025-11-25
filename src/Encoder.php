<?php

declare(strict_types=1);

namespace JimmyAhalpara\ToonFormat;

/**
 * Type-specific encoders for TOON format.
 *
 * Provides encoding functions for different value types: objects, arrays
 * (primitive, tabular, and list formats), and primitives.
 */
class Encoder
{
    private EncodeOptions $options;
    private LineWriter $writer;

    public function __construct(EncodeOptions $options, LineWriter $writer)
    {
        $this->options = $options;
        $this->writer = $writer;
    }

    /**
     * Encode a value to TOON format.
     *
     * @param mixed $value Normalized value
     * @param int $depth Current indentation depth
     */
    public function encodeValue(mixed $value, int $depth = 0): void
    {
        // Handle empty stdClass specially (kept as object to distinguish from empty array)
        if ($value instanceof \stdClass && empty(get_object_vars($value))) {
            // Empty object at root produces no output
            return;
        }

        if (Normalizer::isJsonPrimitive($value)) {
            $this->writer->push($depth, Primitives::encodePrimitive($value, $this->options->getDelimiter()));
        } elseif (Normalizer::isJsonArray($value)) {
            $this->encodeArray($value, $depth, null);
        } elseif (Normalizer::isJsonObject($value)) {
            $this->encodeObject($value, $depth, null);
        }
    }

    /**
     * Encode an object to TOON format.
     *
     * @param array<string, mixed> $obj Dictionary object
     * @param int $depth Current indentation depth
     * @param string|null $key Optional key name
     */
    public function encodeObject(array $obj, int $depth, ?string $key): void
    {
        // Empty object at root level produces no output
        if (empty($obj) && $key === null && $depth === 0) {
            return;
        }

        if ($key !== null) {
            $this->writer->push($depth, Primitives::encodeKey($key) . Constants::COLON);
        }

        foreach ($obj as $objKey => $objValue) {
            $this->encodeKeyValuePair($objKey, $objValue, $key !== null ? $depth + 1 : $depth);
        }
    }

    /**
     * Encode a key-value pair.
     *
     * @param string $key Key name
     * @param mixed $value Value to encode
     * @param int $depth Current indentation depth
     */
    private function encodeKeyValuePair(string $key, mixed $value, int $depth): void
    {
        if (Normalizer::isJsonPrimitive($value)) {
            $primitiveStr = Primitives::encodePrimitive($value, $this->options->getDelimiter());
            $this->writer->push($depth, Primitives::encodeKey($key) . Constants::COLON . ' ' . $primitiveStr);
        } elseif (Normalizer::isJsonArray($value)) {
            $this->encodeArray($value, $depth, $key);
        } elseif (Normalizer::isJsonObject($value)) {
            $this->encodeObject($value, $depth, $key);
        }
    }

    /**
     * Encode an array to TOON format.
     *
     * @param array<mixed> $arr List array
     * @param int $depth Current indentation depth
     * @param string|null $key Optional key name
     */
    public function encodeArray(array $arr, int $depth, ?string $key): void
    {
        // Handle empty array
        if (empty($arr)) {
            $header = Primitives::formatHeader(
                $key,
                0,
                null,
                $this->options->getDelimiter(),
                $this->options->getLengthMarker()
            );
            $this->writer->push($depth, $header);

            return;
        }

        // Check array type and encode accordingly
        if (Normalizer::isArrayOfPrimitives($arr)) {
            $this->encodeInlinePrimitiveArray($arr, $depth, $key);
        } elseif (Normalizer::isArrayOfArrays($arr)) {
            $this->encodeArrayOfArrays($arr, $depth, $key);
        } elseif (Normalizer::isArrayOfObjects($arr)) {
            $tabularHeader = $this->detectTabularHeader($arr);
            if ($tabularHeader !== null) {
                $this->encodeArrayOfObjectsAsTabular($arr, $tabularHeader, $depth, $key);
            } else {
                $this->encodeMixedArrayAsListItems($arr, $depth, $key);
            }
        } else {
            $this->encodeMixedArrayAsListItems($arr, $depth, $key);
        }
    }

    /**
     * Encode an array of primitives inline.
     *
     * @param array<mixed> $arr Array of primitives
     * @param int $depth Current indentation depth
     * @param string|null $key Optional key name
     */
    private function encodeInlinePrimitiveArray(array $arr, int $depth, ?string $key): void
    {
        $encodedValues = array_map(
            fn ($item) => Primitives::encodePrimitive($item, $this->options->getDelimiter()),
            $arr
        );
        $joined = Primitives::joinEncodedValues($encodedValues, $this->options->getDelimiter());
        $header = Primitives::formatHeader(
            $key,
            count($arr),
            null,
            $this->options->getDelimiter(),
            $this->options->getLengthMarker()
        );
        $this->writer->push($depth, $header . ' ' . $joined);
    }

    /**
     * Encode an array of arrays (nested structure).
     *
     * @param array<array<mixed>> $arr Array of arrays
     * @param int $depth Current indentation depth
     * @param string|null $key Optional key name
     */
    private function encodeArrayOfArrays(array $arr, int $depth, ?string $key): void
    {
        $header = Primitives::formatHeader(
            $key,
            count($arr),
            null,
            $this->options->getDelimiter(),
            $this->options->getLengthMarker()
        );
        $this->writer->push($depth, $header);

        foreach ($arr as $item) {
            if (Normalizer::isArrayOfPrimitives($item)) {
                $encodedValues = array_map(
                    fn ($v) => Primitives::encodePrimitive($v, $this->options->getDelimiter()),
                    $item
                );
                $joined = Primitives::joinEncodedValues($encodedValues, $this->options->getDelimiter());
                $itemHeader = Primitives::formatHeader(
                    null,
                    count($item),
                    null,
                    $this->options->getDelimiter(),
                    $this->options->getLengthMarker()
                );
                $line = Constants::LIST_ITEM_PREFIX . $itemHeader;
                if (! empty($joined)) {
                    $line .= ' ' . $joined;
                }
                $this->writer->push($depth + 1, $line);
            } else {
                $this->encodeArray($item, $depth + 1, null);
            }
        }
    }

    /**
     * Detect if array can use tabular format and return header keys.
     *
     * @param array<array<string, mixed>> $arr Array of objects
     * @return array<string>|null List of keys if tabular, null otherwise
     */
    private function detectTabularHeader(array $arr): ?array
    {
        if (empty($arr)) {
            return null;
        }

        // Get keys from first object
        $firstKeys = array_keys($arr[0]);
        $firstKeysSet = array_flip($firstKeys);

        // Check all objects have same keys and all values are primitives
        foreach ($arr as $obj) {
            if (array_flip(array_keys($obj)) != $firstKeysSet) {
                return null;
            }
            foreach ($obj as $value) {
                if (! Normalizer::isJsonPrimitive($value)) {
                    return null;
                }
            }
        }

        return $firstKeys;
    }

    /**
     * Encode array of uniform objects in tabular format.
     *
     * @param array<array<string, mixed>> $arr Array of uniform objects
     * @param array<string> $fields Field names for header
     * @param int $depth Current indentation depth
     * @param string|null $key Optional key name
     */
    private function encodeArrayOfObjectsAsTabular(array $arr, array $fields, int $depth, ?string $key): void
    {
        $header = Primitives::formatHeader(
            $key,
            count($arr),
            $fields,
            $this->options->getDelimiter(),
            $this->options->getLengthMarker()
        );
        $this->writer->push($depth, $header);

        foreach ($arr as $obj) {
            $rowValues = array_map(
                fn ($field) => Primitives::encodePrimitive($obj[$field], $this->options->getDelimiter()),
                $fields
            );
            $row = Primitives::joinEncodedValues($rowValues, $this->options->getDelimiter());
            $this->writer->push($depth + 1, $row);
        }
    }

    /**
     * Encode mixed array as list items.
     *
     * @param array<mixed> $arr Mixed array
     * @param int $depth Current indentation depth
     * @param string|null $key Optional key name
     */
    private function encodeMixedArrayAsListItems(array $arr, int $depth, ?string $key): void
    {
        $header = Primitives::formatHeader(
            $key,
            count($arr),
            null,
            $this->options->getDelimiter(),
            $this->options->getLengthMarker()
        );
        $this->writer->push($depth, $header);

        foreach ($arr as $item) {
            if (Normalizer::isJsonPrimitive($item)) {
                $this->writer->push(
                    $depth + 1,
                    Constants::LIST_ITEM_PREFIX . Primitives::encodePrimitive($item, $this->options->getDelimiter())
                );
            } elseif (Normalizer::isJsonObject($item)) {
                $this->encodeObjectAsListItem($item, $depth + 1);
            } elseif (Normalizer::isJsonArray($item)) {
                // Arrays as list items
                if (Normalizer::isArrayOfPrimitives($item)) {
                    // Inline primitive array
                    $encodedValues = array_map(
                        fn ($v) => Primitives::encodePrimitive($v, $this->options->getDelimiter()),
                        $item
                    );
                    $joined = Primitives::joinEncodedValues($encodedValues, $this->options->getDelimiter());
                    $header = Primitives::formatHeader(
                        null,
                        count($item),
                        null,
                        $this->options->getDelimiter(),
                        $this->options->getLengthMarker()
                    );
                    $line = Constants::LIST_ITEM_PREFIX . $header;
                    if (! empty($joined)) {
                        $line .= ' ' . $joined;
                    }
                    $this->writer->push($depth + 1, $line);
                } else {
                    // Non-inline array
                    $tabularFields = null;
                    if (Normalizer::isArrayOfObjects($item)) {
                        $tabularFields = $this->detectTabularHeader($item);
                    }
                    $header = Primitives::formatHeader(
                        null,
                        count($item),
                        $tabularFields,
                        $this->options->getDelimiter(),
                        $this->options->getLengthMarker()
                    );
                    $this->writer->push($depth + 1, Constants::LIST_ITEM_PREFIX . $header);
                    $this->encodeArrayContent($item, $depth + 2);
                }
            }
        }
    }

    /**
     * Encode object as a list item.
     *
     * @param array<string, mixed> $obj Object to encode
     * @param int $depth Current indentation depth
     */
    private function encodeObjectAsListItem(array $obj, int $depth): void
    {
        if (empty($obj)) {
            $this->writer->push($depth, rtrim(Constants::LIST_ITEM_PREFIX));

            return;
        }

        // First key-value pair goes on same line as the "-"
        $keys = array_keys($obj);
        $firstKey = $keys[0];
        $firstValue = $obj[$firstKey];

        if (Normalizer::isJsonPrimitive($firstValue)) {
            $encodedVal = Primitives::encodePrimitive($firstValue, $this->options->getDelimiter());
            $this->writer->push(
                $depth,
                Constants::LIST_ITEM_PREFIX . Primitives::encodeKey($firstKey) . Constants::COLON . ' ' . $encodedVal
            );
        } elseif (Normalizer::isJsonArray($firstValue)) {
            // Arrays go on the same line with their header
            if (Normalizer::isArrayOfPrimitives($firstValue)) {
                // Inline primitive array
                $encodedValues = array_map(
                    fn ($item) => Primitives::encodePrimitive($item, $this->options->getDelimiter()),
                    $firstValue
                );
                $joined = Primitives::joinEncodedValues($encodedValues, $this->options->getDelimiter());
                $header = Primitives::formatHeader(
                    $firstKey,
                    count($firstValue),
                    null,
                    $this->options->getDelimiter(),
                    $this->options->getLengthMarker()
                );
                $line = Constants::LIST_ITEM_PREFIX . $header;
                if (! empty($joined)) {
                    $line .= ' ' . $joined;
                }
                $this->writer->push($depth, $line);
            } else {
                // Non-inline array
                $tabularFields = null;
                if (Normalizer::isArrayOfObjects($firstValue)) {
                    $tabularFields = $this->detectTabularHeader($firstValue);
                }
                $header = Primitives::formatHeader(
                    $firstKey,
                    count($firstValue),
                    $tabularFields,
                    $this->options->getDelimiter(),
                    $this->options->getLengthMarker()
                );
                $this->writer->push($depth, Constants::LIST_ITEM_PREFIX . $header);
                $this->encodeArrayContent($firstValue, $depth + 1);
            }
        } else {
            // If first value is an object, put "-" alone then encode normally
            $this->writer->push($depth, rtrim(Constants::LIST_ITEM_PREFIX));
            $this->encodeKeyValuePair($firstKey, $firstValue, $depth + 1);
        }

        // Rest of the keys go normally indented
        for ($i = 1; $i < count($keys); $i++) {
            $key = $keys[$i];
            $this->encodeKeyValuePair($key, $obj[$key], $depth + 1);
        }
    }

    /**
     * Encode array content (for nested arrays in list items).
     *
     * @param array<mixed> $arr Array to encode
     * @param int $depth Current indentation depth
     */
    private function encodeArrayContent(array $arr, int $depth): void
    {
        if (empty($arr)) {
            return;
        }

        if (Normalizer::isArrayOfObjects($arr)) {
            $tabularHeader = $this->detectTabularHeader($arr);
            if ($tabularHeader !== null) {
                // Tabular format
                foreach ($arr as $obj) {
                    $rowValues = array_map(
                        fn ($field) => Primitives::encodePrimitive($obj[$field], $this->options->getDelimiter()),
                        $tabularHeader
                    );
                    $row = Primitives::joinEncodedValues($rowValues, $this->options->getDelimiter());
                    $this->writer->push($depth, $row);
                }

                return;
            }
        }

        // List format or other types
        foreach ($arr as $item) {
            if (Normalizer::isJsonPrimitive($item)) {
                $this->writer->push(
                    $depth,
                    Constants::LIST_ITEM_PREFIX . Primitives::encodePrimitive($item, $this->options->getDelimiter())
                );
            } elseif (Normalizer::isJsonObject($item)) {
                $this->encodeObjectAsListItem($item, $depth);
            } elseif (Normalizer::isJsonArray($item)) {
                $this->encodeArray($item, $depth, null);
            }
        }
    }
}
