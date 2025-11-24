<?php

declare(strict_types=1);

namespace JimmyAhalpara\ToonFormat;

/**
 * Constants used throughout TOON encoding and decoding.
 */
final class Constants
{
    // Character constants
    public const COLON = ':';
    public const COMMA = ',';
    public const TAB = "\t";
    public const PIPE = '|';
    public const DOUBLE_QUOTE = '"';
    public const BACKSLASH = '\\';
    public const OPEN_BRACKET = '[';
    public const CLOSE_BRACKET = ']';
    public const OPEN_BRACE = '{';
    public const CLOSE_BRACE = '}';
    public const LIST_ITEM_MARKER = '- ';
    public const LIST_ITEM_PREFIX = '- ';

    // Literal values
    public const NULL_LITERAL = 'null';
    public const TRUE_LITERAL = 'true';
    public const FALSE_LITERAL = 'false';

    // Default configuration
    public const DEFAULT_DELIMITER = self::COMMA;
    public const DEFAULT_INDENT = 2;

    // Regex patterns
    public const NUMERIC_REGEX = '/^-?(?:0|[1-9]\d*)(?:\.\d+)?(?:[eE][+-]?\d+)?$/';
    public const OCTAL_REGEX = '/^0[0-7]+$/';
    public const VALID_KEY_REGEX = '/^[A-Z_][\w.]*$/i';

    // Characters that require quoting in strings
    public const STRUCTURAL_CHARS_REGEX = '/[\[\]\{\}\-:]/';
    public const CONTROL_CHARS_REGEX = '/[\x00-\x1F\x7F]/';

    // Delimiters mapping
    public const DELIMITERS = [
        'comma' => self::COMMA,
        'tab' => self::TAB,
        'pipe' => self::PIPE,
    ];

    private function __construct()
    {
        // Prevent instantiation
    }
}
