# TOON Format Specification (PHP)

Token-Oriented Object Notation (TOON) format specification adapted for PHP.

## Overview

TOON is a compact serialization format optimized for LLM contexts, achieving 30-60% token reduction compared to JSON while maintaining human readability.

**Key Features:**
- YAML-like indentation-based structure
- CSV-style tabular arrays for uniform data
- Inline arrays for short lists
- Automatic type inference
- Length validation

---

## Primitives

### Strings

**Unquoted strings** (default):
```
name: Alice
```

**Quoted strings** (when needed):
- Leading/trailing whitespace
- Structural characters: `[`, `]`, `{`, `}`, `:`, `-`
- Escape sequences
- Starts with quote character

```
title: "  Spaced  "
id: "[ABC-123]"
path: "C:\\Users\\Name"
quote: 'He said "hi"'
```

**Escape sequences:**
- `\\` → `\`
- `\"` → `"`
- `\'` → `'`
- `\n` → newline
- `\r` → carriage return
- `\t` → tab
- `\uXXXX` → Unicode character

### Numbers

Integers and floats:
```
age: 30
price: 19.99
negative: -5
scientific: 1.23e10
```

### Booleans

```
active: true
disabled: false
```

### Null

```
value: null
empty:
```

Note: Empty value after `:` is treated as `null`.

---

## Objects

### Simple Objects

Key-value pairs with indentation:

```toon
user:
  name: Alice
  age: 30
  active: true
```

PHP equivalent:
```php
[
    'user' => [
        'name' => 'Alice',
        'age' => 30,
        'active' => true
    ]
]
```

### Nested Objects

```toon
company:
  name: TechCorp
  address:
    street: 123 Main St
    city: Springfield
    zip: 12345
  employees: 50
```

### Empty Objects

```toon
data: {}
```

---

## Arrays

TOON supports three array formats optimized for different use cases.

### 1. Inline Arrays

For short, simple lists:

**Syntax:** `[length,]: value1,value2,value3`

```toon
numbers[3]: 1,2,3
colors[2]: red,blue
tags[4]: php,toon,library,format
```

PHP equivalent:
```php
[
    'numbers' => [1, 2, 3],
    'colors' => ['red', 'blue'],
    'tags' => ['php', 'toon', 'library', 'format']
]
```

**Delimiter variants:**
```toon
tabs[3	]: a	b	c
pipes[3|]: x|y|z
```

### 2. List Arrays

For longer lists or complex items:

**Syntax:**
```toon
key[length]:
  - item1
  - item2
```

**Example:**
```toon
tasks[3]:
  - Buy groceries
  - Call dentist
  - Finish report
```

PHP equivalent:
```php
[
    'tasks' => [
        'Buy groceries',
        'Call dentist',
        'Finish report'
    ]
]
```

**Nested objects in lists:**
```toon
users[2]:
  - name: Alice
    age: 30
  - name: Bob
    age: 25
```

### 3. Tabular Arrays

For arrays of uniform objects (CSV-style):

**Syntax:**
```toon
key[length,]{field1,field2}:
  value1_1,value1_2
  value2_1,value2_2
```

**Example:**
```toon
users[3,]{id,name,age}:
  1,Alice,30
  2,Bob,25
  3,Charlie,35
```

PHP equivalent:
```php
[
    'users' => [
        ['id' => 1, 'name' => 'Alice', 'age' => 30],
        ['id' => 2, 'name' => 'Bob', 'age' => 25],
        ['id' => 3, 'name' => 'Charlie', 'age' => 35]
    ]
]
```

**Token savings:**

JSON (168 tokens):
```json
{
  "users": [
    {"id": 1, "name": "Alice", "age": 30},
    {"id": 2, "name": "Bob", "age": 25},
    {"id": 3, "name": "Charlie", "age": 35}
  ]
}
```

TOON (68 tokens):
```toon
users[3,]{id,name,age}:
  1,Alice,30
  2,Bob,25
  3,Charlie,35
```

**60% reduction!**

---

## Indentation Rules

**Standard:** 2 spaces per level

```toon
root:
  level1:
    level2:
      value: deep
```

**Custom indent:**

```php
$options = new EncodeOptions(indent: 4);
ToonFormat::encode($data, $options);
```

**Rules:**
- Use spaces, not tabs (for consistency)
- Indentation indicates nesting level
- Parent key must be at lower indentation than children

---

## Length Validation

### Array Lengths

Encoded lengths in `[n]` or `[n,]` are validated:

```toon
items[5]: a,b,c,d,e  ✅ Valid
items[5]: a,b,c      ❌ ToonDecodeException (strict mode)
```

**Strict vs Lenient:**

```php
// Strict (default) - enforces lengths
$options = new DecodeOptions(strict: true);
ToonFormat::decode('items[5]: a,b,c', $options); // Throws exception

// Lenient - accepts mismatches
$options = new DecodeOptions(strict: false);
ToonFormat::decode('items[5]: a,b,c', $options); // Returns ['items' => ['a', 'b', 'c']]
```

### Length Markers

Optional `#` prefix for clarity:

```toon
[#100,]: ...  // Explicit marker
[100,]: ...   // Equivalent
```

Enable during encoding:

```php
$options = new EncodeOptions(lengthMarker: '#');
ToonFormat::encode($data, $options);
```

---

## Delimiters

### Available Delimiters

1. **Comma** (default): `,`
2. **Tab**: `\t`
3. **Pipe**: `|`

### Delimiter in Headers

Arrays show delimiter in header to indicate format:

```toon
[3,]: a,b,c      # Comma delimiter
[3	]: a	b	c    # Tab delimiter
[3|]: a|b|c      # Pipe delimiter
```

**Note:** Only shown for non-comma delimiters in primitive arrays (default behavior hides comma). Tabular arrays always show delimiter.

### Choosing Delimiters

**Comma** - Best for most data:
```php
ToonFormat::encode(['a', 'b', 'c']);
```

**Tab** - When data contains commas:
```php
$addresses = ['123 Main St, NY', '456 Oak Ave, LA'];
ToonFormat::encode($addresses, ['delimiter' => "\t"]);
// [2	]: 123 Main St, NY	456 Oak Ave, LA
```

**Pipe** - Alternative for clarity:
```php
ToonFormat::encode([1, 2, 3], ['delimiter' => '|']);
// [3|]: 1|2|3
```

---

## Edge Cases

### Empty Values

```toon
nullValue: null
emptyString: ""
emptyArray: []
emptyObject: {}
```

### Special Numbers

```php
$data = [
    'infinity' => INF,
    'neg_infinity' => -INF,
    'not_a_number' => NAN,
];

ToonFormat::encode($data);
// infinity: null
// neg_infinity: null
// not_a_number: null
```

### DateTime Objects

```php
$data = ['timestamp' => new DateTime('2024-01-15 10:30:00')];
ToonFormat::encode($data);
// timestamp: "2024-01-15T10:30:00+00:00"
```

### Mixed Arrays

If array contains both indexed and associative elements, it's treated as object:

```php
$data = ['a', 'key' => 'value'];
ToonFormat::encode($data);
// 0: a
// key: value
```

---

## Comparison with JSON

### Example Dataset

```php
$data = [
    'users' => [
        ['id' => 1, 'name' => 'Alice', 'active' => true],
        ['id' => 2, 'name' => 'Bob', 'active' => false],
        ['id' => 3, 'name' => 'Charlie', 'active' => true],
    ],
    'count' => 3,
];
```

### JSON (230 tokens)

```json
{
  "users": [
    {
      "id": 1,
      "name": "Alice",
      "active": true
    },
    {
      "id": 2,
      "name": "Bob",
      "active": false
    },
    {
      "id": 3,
      "name": "Charlie",
      "active": true
    }
  ],
  "count": 3
}
```

### TOON (92 tokens)

```toon
users[3,]{id,name,active}:
  1,Alice,true
  2,Bob,false
  3,Charlie,true
count: 3
```

**60% reduction!**

---

## Grammar (Informal)

```
document := line*

line := key ":" value
      | key array_header
      | list_item
      | tabular_row

key := unquoted_string | quoted_string

value := primitive
       | inline_array
       | object
       | empty

array_header := "[" length delimiter? "]" fields? ":"

fields := "{" key ("," key)* "}"

inline_array := "[" length delimiter? "]:" value (delimiter value)*

list_item := "-" value

tabular_row := value (delimiter value)*

primitive := string | number | boolean | null
```

---

## PHP-Specific Behavior

### Type Handling

- `stdClass` → associative array
- `DateTime` → ISO 8601 string
- `JsonSerializable` → `jsonSerialize()` result
- Numeric string keys preserved as strings

### Strict Mode

```php
// Default: strict validation
ToonFormat::decode($input);

// Lenient parsing
ToonFormat::decode($input, ['strict' => false]);
```

---

## See Also

- [API Reference](api.md) - Complete function documentation
- [README](../README.md) - Installation and quick start
- [TOON Spec](https://github.com/toon-format/spec) - Official specification
