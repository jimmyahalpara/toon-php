# TOON Format for PHP

[![Tests](https://github.com/jimmyahalpara/toon-php/actions/workflows/test.yml/badge.svg)](https://github.com/jimmyahalpara/toon-php/actions)
[![PHP Version](https://img.shields.io/packagist/php-v/jimmyahalpara/toon-php)](https://packagist.org/packages/jimmyahalpara/toon-php)

> **⚠️ Beta Status (v0.9.x):** This library is in active development and working towards spec compliance. API may change before 1.0.0 release.

Compact, human-readable serialization format for LLM contexts with **30-60% token reduction** vs JSON. Combines YAML-like indentation with CSV-like tabular arrays. Working towards full compatibility with the [official TOON specification](https://github.com/toon-format/spec).

**Key Features:** Minimal syntax • Tabular arrays for uniform data • Array length validation • PHP 8.0+ • Comprehensive test coverage.

```bash
composer require jimmyahalpara/toon-php
```

## Quick Start

```php
<?php

use JimmyAhalpara\ToonFormat\ToonFormat;

// Simple object
$result = ToonFormat::encode(['name' => 'Alice', 'age' => 30]);
// name: Alice
// age: 30

// Tabular array (uniform objects)
$data = [
    ['id' => 1, 'name' => 'Alice'],
    ['id' => 2, 'name' => 'Bob']
];
echo ToonFormat::encode($data);
// [2,]{id,name}:
//   1,Alice
//   2,Bob

// Decode back to PHP
$decoded = ToonFormat::decode("items[2]: apple,banana");
// ['items' => ['apple', 'banana']]
```

## API Reference

### `ToonFormat::encode($value, $options = [])`

```php
ToonFormat::encode(['id' => 123], [
    'delimiter' => "\t",
    'indent' => 4,
    'lengthMarker' => '#'
]);
```

**Options:**
- `delimiter`: `","` (default), `"\t"`, `"|"`
- `indent`: Spaces per level (default: `2`)
- `lengthMarker`: `false` (default) or `"#"` to prefix array lengths

### `ToonFormat::decode($input, $options = [])`

```php
ToonFormat::decode("id: 123", [
    'indent' => 2,
    'strict' => true
]);
```

**Options:**
- `indent`: Expected indent size (default: `2`)
- `strict`: Validate syntax, lengths, delimiters (default: `true`)

## Format Specification

| Type | Example Input | TOON Output |
|------|---------------|-------------|
| **Object** | `['name' => 'Alice', 'age' => 30]` | `name: Alice`<br>`age: 30` |
| **Primitive Array** | `[1, 2, 3]` | `[3]: 1,2,3` |
| **Tabular Array** | `[['id' => 1, 'name' => 'A'], ['id' => 2, 'name' => 'B']]` | `[2,]{id,name}:`<br>&nbsp;&nbsp;`1,A`<br>&nbsp;&nbsp;`2,B` |
| **Mixed Array** | `[['x' => 1], 42, 'hi']` | `[3]:`<br>&nbsp;&nbsp;`- x: 1`<br>&nbsp;&nbsp;`- 42`<br>&nbsp;&nbsp;`- hi` |

**Quoting:** Only when necessary (empty, keywords, numeric strings, whitespace, structural chars, delimiters)

**Type Normalization:** `INF/NAN` → `null` • `DateTime` → ISO 8601 • `-0` → `0`

## Development

```bash
# Install dependencies
composer install

# Run tests
composer test

# Run tests with coverage
composer test:coverage

# Static analysis
composer phpstan

# Code style check
composer cs:check

# Fix code style
composer cs:fix
```

## Documentation

- [📘 Full Documentation](docs/) - Complete guides and references
- [🔧 API Reference](docs/api.md) - Detailed function documentation
- [📋 Format Specification](docs/format.md) - TOON syntax and rules
- [📜 TOON Spec](https://github.com/toon-format/spec) - Official specification

## License

MIT License – see [LICENSE](LICENSE) for details
