<?php

declare(strict_types=1);

namespace JimmyAhalpara\ToonFormat;

use JimmyAhalpara\ToonFormat\Exception\ToonDecodeException;

/**
 * TOON decoder implementation.
 *
 * Converts TOON format strings back to PHP values.
 */
class Decoder
{
    private DecodeOptions $options;

    public function __construct(DecodeOptions $options)
    {
        $this->options = $options;
    }

    /**
     * Decode a TOON-formatted string to a PHP value.
     *
     * @param string $input TOON-formatted string
     * @return mixed Decoded PHP value
     * @throws ToonDecodeException
     */
    public function decode(string $input): mixed
    {
        $input = trim($input);
        
        if ($input === '') {
            return [];
        }

        $lines = explode("\n", $input);
        $parsedLines = $this->parseLines($lines);

        if (empty($parsedLines)) {
            return [];
        }

        // Check if root is an array header (without a key)
        $firstLine = $parsedLines[0];
        
        if ($this->isArrayHeader($firstLine['content'])) {
            $header = $this->parseArrayHeader($firstLine['content']);
            
            // Root array (no key or empty key)
            if ($header['key'] === null || $header['key'] === '') {
                // For root arrays, the header has depth 0, content has depth 1
                // Pass headerDepth = 0 so expectedDepth = 0 + 1 = 1
                return $this->decodeArrayContent($parsedLines, 1, 0, $header);
            }
        }

        // Check if single primitive
        if (count($parsedLines) === 1 && !str_contains($firstLine['content'], ':')) {
            return $this->parsePrimitive($firstLine['content']);
        }

        // Otherwise, root object
        return $this->decodeObject($parsedLines, 0, -1);
    }

    /**
     * Parse lines and extract depth information.
     *
     * @param array<string> $lines
     * @return array<array{depth: int, content: string, raw: string}>
     */
    private function parseLines(array $lines): array
    {
        $parsed = [];
        $indentSize = $this->options->getIndent();

        foreach ($lines as $lineNum => $line) {
            // Skip blank lines
            if (trim($line) === '') {
                continue;
            }

            // Calculate depth
            $depth = 0;
            $trimmed = ltrim($line);
            $spaces = strlen($line) - strlen($trimmed);
            
            if ($spaces > 0 && $indentSize > 0) {
                $depth = (int)($spaces / $indentSize);
            }

            $parsed[] = [
                'depth' => $depth,
                'content' => $trimmed,
                'raw' => $line,
                'line' => $lineNum + 1,
            ];
        }

        return $parsed;
    }

    /**
     * Check if content is an array header.
     * Matches patterns like: [3]:, [3,]:, [3,]{fields}:, [3	]: etc.
     */
    private function isArrayHeader(string $content): bool
    {
        // Check if starts with [ and contains : somewhere
        // This covers: [n]:, [n,]:, [n,]{fields}:, [n	]:, [n|]:
        return preg_match('/^\[/', $content) === 1 && str_contains($content, ':');
    }

    /**
     * Decode an object.
     *
     * @param array<array> $lines
     * @param int $startIdx
     * @param int $parentDepth
     * @return array<string, mixed>
     */
    private function decodeObject(array $lines, int $startIdx, int $parentDepth): array
    {
        $result = [];
        $i = $startIdx;
        $expectedDepth = $parentDepth + 1;

        while ($i < count($lines)) {
            $line = $lines[$i];

            if ($line['depth'] < $expectedDepth) {
                break;
            }

            if ($line['depth'] > $expectedDepth) {
                $i++;
                continue;
            }

            $content = $line['content'];

            // Check for array header with key first
            if (preg_match('/^([^:\[]+)\[/', $content)) {
                $header = $this->parseArrayHeader($content);
                if ($header['key'] !== null) {
                    $array = $this->decodeArrayContent($lines, $i + 1, $line['depth'], $header);
                    $result[$header['key']] = $array;
                    // Skip array content
                    $arrayItemDepth = $line['depth'] + 1;
                    $i++;
                    while ($i < count($lines) && $lines[$i]['depth'] >= $arrayItemDepth) {
                        $i++;
                    }
                    continue;
                }
            }

            // Parse key-value
            if (str_contains($content, ':')) {
                [$key, $value] = $this->splitKeyValue($content);
                
                if ($value === '' || $value === ':') {
                    // Nested object
                    $result[$key] = $this->decodeObject($lines, $i + 1, $line['depth']);
                    $i++;
                    while ($i < count($lines) && $lines[$i]['depth'] > $line['depth']) {
                        $i++;
                    }
                } else {
                    $result[$key] = $this->parsePrimitive($value);
                    $i++;
                }
            } else {
                $i++;
            }
        }

        return $result;
    }

    /**
     * Decode an array with optional key.
     *
     * @return array{0: string|null, 1: array, 2: int}
     */
    private function decodeArrayWithKey(array $lines, int $idx, int $depth): array
    {
        $line = $lines[$idx];
        $content = $line['content'];

        // Parse header
        $header = $this->parseArrayHeader($content);
        $array = $this->decodeArrayContent($lines, $idx + 1, $depth, $header);

        return [$header['key'], $array, $idx + 1 + count($array)];
    }

    /**
     * Decode an array (root array).
     */
    private function decodeArray(array $lines, int $startIdx, int $parentDepth): array
    {
        $line = $lines[$startIdx];
        $header = $this->parseArrayHeader($line['content']);
        return $this->decodeArrayContent($lines, $startIdx + 1, $line['depth'], $header);
    }

    /**
     * Parse array header.
     *
     * @return array{key: string|null, length: int, delimiter: string, fields: array<string>|null, inline: string|null}
     */
    private function parseArrayHeader(string $content): array
    {
        $key = null;
        $delimiter = ',';
        $fields = null;
        $inline = null;

        // Check if this starts with a bracket (no key)
        $startsWithBracket = preg_match('/^\[/', $content);
        
        // Extract key if present (not starting with bracket)
        if (!$startsWithBracket && preg_match('/^(.+?)\[/', $content, $matches)) {
            $keyPart = trim($matches[1]);
            if ($keyPart !== '') {
                $key = $this->parseKey($keyPart);
            }
        }

        // Extract bracket content
        if (preg_match('/\[([^\]]+)\]/', $content, $matches)) {
            $bracketContent = $matches[1];
            
            // Check for delimiter
            if (str_ends_with($bracketContent, "\t")) {
                $delimiter = "\t";
                $bracketContent = rtrim($bracketContent, "\t");
            } elseif (str_ends_with($bracketContent, '|')) {
                $delimiter = '|';
                $bracketContent = rtrim($bracketContent, '|');
            } elseif (str_ends_with($bracketContent, ',')) {
                $delimiter = ',';
                $bracketContent = rtrim($bracketContent, ',');
            }

            // Remove # marker if present
            $bracketContent = ltrim($bracketContent, '#');
            $length = (int)$bracketContent;
        } else {
            $length = 0;
        }

        // Extract fields if present
        if (preg_match('/\{([^}]+)\}/', $content, $matches)) {
            $fieldsStr = $matches[1];
            $fields = array_map(
                fn($f) => $this->parseKey(trim($f)),
                explode($delimiter, $fieldsStr)
            );
        }

        // Extract inline content after colon
        if (preg_match('/:(.*)$/', $content, $matches)) {
            $afterColon = trim($matches[1]);
            if ($afterColon !== '') {
                $inline = $afterColon;
            }
        }

        return [
            'key' => $key,
            'length' => $length,
            'delimiter' => $delimiter,
            'fields' => $fields,
            'inline' => $inline,
        ];
    }

    /**
     * Decode array content.
     *
     * @param array<array> $lines
     * @return array<mixed>
     */
    private function decodeArrayContent(array $lines, int $startIdx, int $headerDepth, array $header): array
    {
        // Inline array
        if ($header['inline'] !== null && $header['inline'] !== '') {
            return $this->parseInlineArray($header['inline'], $header['delimiter']);
        }

        // Empty array
        if ($header['length'] === 0) {
            return [];
        }

        // Tabular array
        if ($header['fields'] !== null) {
            return $this->decodeTabularArray($lines, $startIdx, $headerDepth, $header);
        }

        // List array
        return $this->decodeListArray($lines, $startIdx, $headerDepth, $header);
    }

    /**
     * Parse inline array.
     *
     * @return array<mixed>
     */
    private function parseInlineArray(string $content, string $delimiter): array
    {
        $values = explode($delimiter, $content);
        return array_map(fn($v) => $this->parsePrimitive(trim($v)), $values);
    }

    /**
     * Decode tabular array.
     *
     * @return array<array<string, mixed>>
     */
    private function decodeTabularArray(array $lines, int $startIdx, int $headerDepth, array $header): array
    {
        $result = [];
        $expectedDepth = $headerDepth + 1;
        $fields = $header['fields'] ?? [];

        for ($i = $startIdx; $i < count($lines); $i++) {
            $line = $lines[$i];
            
            if ($line['depth'] < $expectedDepth) {
                break;
            }
            
            if ($line['depth'] !== $expectedDepth) {
                continue;
            }

            $values = explode($header['delimiter'], $line['content']);
            $obj = [];
            
            foreach ($fields as $idx => $field) {
                $obj[$field] = isset($values[$idx]) ? $this->parsePrimitive(trim($values[$idx])) : null;
            }
            
            $result[] = $obj;
        }

        return $result;
    }

    /**
     * Decode list array.
     *
     * @return array<mixed>
     */
    private function decodeListArray(array $lines, int $startIdx, int $headerDepth, array $header): array
    {
        $result = [];
        $expectedDepth = $headerDepth + 1;

        for ($i = $startIdx; $i < count($lines); $i++) {
            $line = $lines[$i];
            
            if ($line['depth'] < $expectedDepth) {
                break;
            }
            
            if ($line['depth'] !== $expectedDepth) {
                continue;
            }

            $content = $line['content'];
            
            if (str_starts_with($content, '- ')) {
                $itemContent = substr($content, 2);
                
                // Check if array header
                if ($this->isArrayHeader($itemContent)) {
                    $itemHeader = $this->parseArrayHeader($itemContent);
                    $result[] = $this->decodeArrayContent($lines, $i + 1, $line['depth'], $itemHeader);
                } elseif (str_contains($itemContent, ':')) {
                    // Object
                    $result[] = $this->decodeObjectItem($lines, $i, $line['depth'], $itemContent);
                } else {
                    // Primitive
                    $result[] = $this->parsePrimitive($itemContent);
                }
            }
        }

        return $result;
    }

    /**
     * Decode an object item in a list.
     *
     * @return array<string, mixed>
     */
    private function decodeObjectItem(array $lines, int $idx, int $depth, string $firstLine): array
    {
        $obj = [];
        
        // Parse first line
        [$key, $value] = $this->splitKeyValue($firstLine);
        $obj[$key] = $value !== '' ? $this->parsePrimitive($value) : null;

        // Parse remaining fields
        for ($i = $idx + 1; $i < count($lines); $i++) {
            $line = $lines[$i];
            
            if ($line['depth'] <= $depth) {
                break;
            }
            
            if ($line['depth'] !== $depth + 1) {
                continue;
            }

            $content = $line['content'];
            
            if (str_contains($content, ':')) {
                [$k, $v] = $this->splitKeyValue($content);
                $obj[$k] = $v !== '' ? $this->parsePrimitive($v) : null;
            }
        }

        return $obj;
    }

    /**
     * Split key-value pair.
     *
     * @return array{0: string, 1: string}
     */
    private function splitKeyValue(string $line): array
    {
        $colonPos = strpos($line, ':');
        
        if ($colonPos === false) {
            throw new ToonDecodeException('Missing colon after key');
        }

        $key = $this->parseKey(trim(substr($line, 0, $colonPos)));
        $value = trim(substr($line, $colonPos + 1));

        return [$key, $value];
    }

    /**
     * Parse a key (quoted or unquoted).
     */
    private function parseKey(string $key): string
    {
        $key = trim($key);
        
        if (str_starts_with($key, '"') && str_ends_with($key, '"')) {
            return StringUtils::unescapeString(substr($key, 1, -1));
        }

        return $key;
    }

    /**
     * Parse a primitive value.
     */
    private function parsePrimitive(string $token): mixed
    {
        $token = trim($token);

        // Quoted string
        if (str_starts_with($token, '"')) {
            if (!str_ends_with($token, '"')) {
                throw new ToonDecodeException('Unterminated string: missing closing quote');
            }
            return StringUtils::unescapeString(substr($token, 1, -1));
        }

        // Boolean and null literals
        if ($token === 'true') {
            return true;
        }
        if ($token === 'false') {
            return false;
        }
        if ($token === 'null') {
            return null;
        }

        // Try to parse as number
        if (is_numeric($token)) {
            if (str_contains($token, '.') || str_contains($token, 'e') || str_contains($token, 'E')) {
                return (float)$token;
            }
            return (int)$token;
        }

        // Unquoted string
        return $token;
    }
}
