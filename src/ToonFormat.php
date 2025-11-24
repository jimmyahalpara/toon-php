<?php

declare(strict_types=1);

namespace JimmyAhalpara\ToonFormat;

use JimmyAhalpara\ToonFormat\Exception\ToonDecodeException;

/**
 * TOON Format for PHP.
 *
 * Token-Oriented Object Notation (TOON) is a compact, human-readable serialization
 * format optimized for LLM contexts. Achieves 30-60% token reduction vs JSON while
 * maintaining readability and structure.
 */
class ToonFormat
{
    /**
     * Encode a PHP value into TOON format.
     *
     * @param mixed $value The value to encode (must be JSON-serializable)
     * @param array<string, mixed>|EncodeOptions|null $options Optional encoding options
     * @return string TOON-formatted string
     *
     * @throws \InvalidArgumentException If value contains non-normalizable types
     */
    public static function encode(mixed $value, array|EncodeOptions|null $options = null): string
    {
        // Normalize the value first
        $normalized = Normalizer::normalizeValue($value);

        // Resolve options
        $resolvedOptions = self::resolveEncodeOptions($options);

        // Create writer and encoder
        $writer = new LineWriter($resolvedOptions->getIndent());
        $encoder = new Encoder($resolvedOptions, $writer);

        // Encode the value
        $encoder->encodeValue($normalized, 0);

        return $writer->toString();
    }

    /**
     * Decode a TOON-formatted string to a PHP value.
     *
     * @param string $input TOON-formatted string
     * @param array<string, mixed>|DecodeOptions|null $options Optional decoding options
     * @return mixed Decoded PHP value
     *
     * @throws ToonDecodeException If input is malformed
     */
    public static function decode(string $input, array|DecodeOptions|null $options = null): mixed
    {
        // TODO: Implement decoder
        throw new \RuntimeException('Decoder not yet implemented');
    }

    /**
     * Resolve encoding options with defaults.
     *
     * @param array<string, mixed>|EncodeOptions|null $options
     */
    private static function resolveEncodeOptions(array|EncodeOptions|null $options): EncodeOptions
    {
        if ($options === null) {
            return new EncodeOptions();
        }

        if ($options instanceof EncodeOptions) {
            return $options;
        }

        return EncodeOptions::fromArray($options);
    }

    /**
     * Resolve decoding options with defaults.
     *
     * @param array<string, mixed>|DecodeOptions|null $options
     */
    private static function resolveDecodeOptions(array|DecodeOptions|null $options): DecodeOptions
    {
        if ($options === null) {
            return new DecodeOptions();
        }

        if ($options instanceof DecodeOptions) {
            return $options;
        }

        return DecodeOptions::fromArray($options);
    }
}
