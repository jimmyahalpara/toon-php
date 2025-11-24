# API Reference

Complete API documentation for TOON Format PHP package.

## Core Functions

### `ToonFormat::encode($value, $options = [])`

Converts a PHP value to TOON format string.

**Parameters:**
- `$value` (mixed): JSON-serializable PHP value (array, object, primitives, or nested structures)
- `$options` (array|EncodeOptions, optional): Encoding configuration

**Returns:** `string` - TOON-formatted string

**Examples:**

```php
use JimmyAhalpara\ToonFormat\ToonFormat;

// Simple encoding
ToonFormat::encode(['name' => 'Alice', 'age' => 30]);
// name: Alice
// age: 30

// With options (array)
ToonFormat::encode([1, 2, 3], ['delimiter' => "\t"]);
// [3	]: 1	2	3

// With EncodeOptions object
use JimmyAhalpara\ToonFormat\EncodeOptions;
$options = new EncodeOptions(indent: 4, delimiter: '|', lengthMarker: '#');
ToonFormat::encode([1, 2, 3], $options);
// [#3|]: 1|2|3
```

---

### `ToonFormat::decode($input, $options = [])`

Converts a TOON-formatted string back to PHP values.

**Parameters:**
- `$input` (string): TOON-formatted string
- `$options` (array|DecodeOptions, optional): Decoding configuration

**Returns:** `mixed` - PHP value (array or primitive)

**Throws:** `ToonDecodeException` - On syntax errors or validation failures

**Examples:**

```php
use JimmyAhalpara\ToonFormat\ToonFormat;

// Simple decoding
ToonFormat::decode("name: Alice\nage: 30");
// ['name' => 'Alice', 'age' => 30]

// Tabular arrays
ToonFormat::decode("[2,]{id,name}:\n  1,Alice\n  2,Bob");
// [['id' => 1, 'name' => 'Alice'], ['id' => 2, 'name' => 'Bob']]

// With options
use JimmyAhalpara\ToonFormat\DecodeOptions;
$options = new DecodeOptions(indent: 4, strict: false);
ToonFormat::decode("  item: value", $options);
```

---

## Options Classes

### `EncodeOptions`

Configuration for encoding behavior.

**Constructor:**
```php
new EncodeOptions(
    int $indent = 2,
    string $delimiter = ',',
    string|false $lengthMarker = false
)
```

**Parameters:**
- `$indent` (int): Spaces per indentation level (default: `2`)
- `$delimiter` (string): Array value separator
  - `","` - Comma (default)
  - `"\t"` - Tab
  - `"|"` - Pipe
- `$lengthMarker` (string|false): Prefix for array lengths
  - `false` - No marker (default)
  - `"#"` - Add `#` prefix (e.g., `[#5]`)

**Example:**

```php
$options = new EncodeOptions(
    indent: 4,
    delimiter: "\t",
    lengthMarker: '#'
);

$data = [['id' => 1], ['id' => 2]];
echo ToonFormat::encode($data, $options);
// [#2	]{id}:
//     1
//     2
```

---

### `DecodeOptions`

Configuration for decoding behavior.

**Constructor:**
```php
new DecodeOptions(
    int $indent = 2,
    bool $strict = true
)
```

**Parameters:**
- `$indent` (int): Expected spaces per indentation level (default: `2`)
- `$strict` (bool): Enable strict validation (default: `true`)

**Strict Mode:**

When `$strict = true`, the decoder enforces:
- Indentation must be consistent multiples of `$indent` value
- Array lengths must match actual element count
- Delimiter consistency across rows

When `$strict = false`:
- Lenient indentation
- Array length mismatches allowed

**Example:**

```php
$options = new DecodeOptions(indent: 2, strict: true);

try {
    $result = ToonFormat::decode("items[5]: a,b,c", $options);
} catch (ToonDecodeException $e) {
    echo "Error: " . $e->getMessage(); // Length mismatch
}

// Lenient parsing
$options = new DecodeOptions(strict: false);
$result = ToonFormat::decode("items[5]: a,b,c", $options);
// ['items' => ['a', 'b', 'c']] // Accepts mismatch
```

---

## Exception Handling

### `ToonDecodeException`

Exception raised when decoding fails.

**Properties:**
- `getMessage()`: Human-readable error description
- `getLineNumber()`: Line number where error occurred (if applicable)

**Example:**

```php
use JimmyAhalpara\ToonFormat\Exception\ToonDecodeException;

try {
    $result = ToonFormat::decode('invalid: ["unclosed');
} catch (ToonDecodeException $e) {
    echo "Error on line " . $e->getLineNumber() . ": " . $e->getMessage();
}
```

---

## Type Normalization

Non-JSON types are automatically normalized during encoding:

| PHP Type | Normalized To | Example |
|----------|---------------|---------|
| `DateTime` | ISO 8601 string | `"2024-01-15T10:30:00+00:00"` |
| `stdClass` | associative array | `['key' => 'value']` |
| `INF` / `-INF` | `null` | `null` |
| `NAN` | `null` | `null` |
| `JsonSerializable` | `jsonSerialize()` result | varies |
| `-0.0` | `0` | `0` |

**Example:**

```php
$data = [
    'timestamp' => new DateTime('2024-01-15 10:30:00'),
    'infinity' => INF,
    'object' => new stdClass(),
];

$toon = ToonFormat::encode($data);
// timestamp: "2024-01-15T10:30:00+00:00"
// infinity: null
```

---

## Advanced Usage

### Working with Delimiters

Use different delimiters based on your data:

```php
// Comma (best for general use)
ToonFormat::encode([1, 2, 3]);
// [3]: 1,2,3

// Tab (for data with commas)
ToonFormat::encode(['a,b', 'c,d'], ['delimiter' => "\t"]);
// [2	]: a,b	c,d

// Pipe (alternative)
ToonFormat::encode([1, 2, 3], ['delimiter' => '|']);
// [3|]: 1|2|3
```

### Length Markers

Add `#` prefix for explicit length indication:

```php
$users = [
    ['id' => 1, 'name' => 'Alice'],
    ['id' => 2, 'name' => 'Bob']
];

// Without marker
ToonFormat::encode($users);
// [2,]{id,name}:
//   1,Alice
//   2,Bob

// With marker
ToonFormat::encode($users, ['lengthMarker' => '#']);
// [#2,]{id,name}:
//   1,Alice
//   2,Bob
```

---

## See Also

- [Format Specification](format.md) - Detailed TOON syntax and rules
- [README](../README.md) - Quick start and installation
- [TOON Spec](https://github.com/toon-format/spec) - Official specification
