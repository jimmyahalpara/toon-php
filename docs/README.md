# Documentation

Complete documentation for the TOON Format PHP package.

## Quick Links

- **[API Reference](api.md)** - Complete function and class documentation
- **[Format Specification](format.md)** - Detailed TOON syntax and rules
- **[Main README](../README.md)** - Installation and quick start guide

## Table of Contents

### Getting Started
1. [Installation](../README.md#installation)
2. [Basic Usage](../README.md#basic-usage)
3. [Quick Examples](../README.md#examples)

### API Documentation
1. [Core Functions](api.md#core-functions)
   - `ToonFormat::encode()`
   - `ToonFormat::decode()`
2. [Options Classes](api.md#options-classes)
   - `EncodeOptions`
   - `DecodeOptions`
3. [Exception Handling](api.md#exception-handling)
   - `ToonDecodeException`

### Format Specification
1. [Primitives](format.md#primitives)
   - Strings, Numbers, Booleans, Null
2. [Objects](format.md#objects)
   - Simple and nested objects
3. [Arrays](format.md#arrays)
   - Inline arrays
   - List arrays
   - Tabular arrays (CSV-style)
4. [Advanced Topics](format.md#indentation-rules)
   - Indentation rules
   - Length validation
   - Delimiters
   - Edge cases

## What is TOON?

TOON (Token-Oriented Object Notation) is a serialization format designed to reduce token consumption in LLM contexts by 30-60% compared to JSON, while maintaining human readability.

**Key Benefits:**
- **Compact**: 30-60% fewer tokens than JSON
- **Readable**: YAML-like indentation, natural syntax
- **Efficient**: CSV-style tabular arrays for uniform data
- **Type-safe**: Automatic type inference and validation

## When to Use TOON

✅ **Good fit:**
- LLM prompts and responses
- API payloads with repeated structures
- Configuration files
- Data interchange between AI systems
- Token-constrained contexts

❌ **Consider alternatives:**
- Binary data (use MessagePack)
- Streaming parsers required (use JSON)
- Legacy system integration (use JSON/XML)
- Maximum performance required (use Protocol Buffers)

## Quick Comparison

### JSON (168 tokens)
```json
{
  "users": [
    {"id": 1, "name": "Alice", "age": 30},
    {"id": 2, "name": "Bob", "age": 25},
    {"id": 3, "name": "Charlie", "age": 35}
  ]
}
```

### TOON (68 tokens)
```toon
users[3,]{id,name,age}:
  1,Alice,30
  2,Bob,25
  3,Charlie,35
```

**60% reduction!**

## Usage Examples

### Basic Encoding

```php
use JimmyAhalpara\ToonFormat\ToonFormat;

$data = [
    'name' => 'Alice',
    'age' => 30,
    'active' => true
];

$toon = ToonFormat::encode($data);
echo $toon;
// name: Alice
// age: 30
// active: true
```

### Decoding

```php
$input = <<<TOON
name: Alice
age: 30
active: true
TOON;

$data = ToonFormat::decode($input);
// ['name' => 'Alice', 'age' => 30, 'active' => true]
```

### Tabular Arrays

```php
$users = [
    ['id' => 1, 'name' => 'Alice'],
    ['id' => 2, 'name' => 'Bob']
];

$toon = ToonFormat::encode($users);
// [2,]{id,name}:
//   1,Alice
//   2,Bob
```

### Custom Options

```php
use JimmyAhalpara\ToonFormat\EncodeOptions;

$options = new EncodeOptions(
    indent: 4,
    delimiter: "\t",
    lengthMarker: '#'
);

$toon = ToonFormat::encode($data, $options);
```

## Error Handling

```php
use JimmyAhalpara\ToonFormat\Exception\ToonDecodeException;

try {
    $result = ToonFormat::decode($malformedInput);
} catch (ToonDecodeException $e) {
    echo "Error on line " . $e->getLineNumber() . ": ";
    echo $e->getMessage();
}
```

## PHP-Specific Features

### Type Normalization
- `DateTime` objects → ISO 8601 strings
- `stdClass` → associative arrays
- `INF`, `NAN` → `null`
- `JsonSerializable` → `jsonSerialize()` result

### Strict vs Lenient Mode
```php
// Strict (default): Validates array lengths
$result = ToonFormat::decode($input);

// Lenient: Accepts length mismatches
$result = ToonFormat::decode($input, ['strict' => false]);
```

## Contributing

See [CONTRIBUTING.md](../CONTRIBUTING.md) for development guidelines.

## License

MIT License - see [LICENSE](../LICENSE) for details.

## Links

- **GitHub**: [jimmyahalpara/toon-php](https://github.com/jimmyahalpara/toon-php)
- **Packagist**: [jimmyahalpara/toon-php](https://packagist.org/packages/jimmyahalpara/toon-php)
- **TOON Spec**: [toon-format/spec](https://github.com/toon-format/spec)
- **Python Implementation**: [toon-format/toon-python](https://github.com/toon-format/toon-python)
