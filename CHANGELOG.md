# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned
- Fix decoder edge cases for root-level tabular arrays
- CLI tool for file conversion
- Performance optimization and benchmarks
- 100% test coverage
- Full TOON specification compliance

## [0.9.0] - 2024-11-24

### Added
- Initial public release
- Complete encoder implementation with tabular array support
- Functional decoder with 91% test coverage (20/22 tests passing)
- Type normalization (DateTime, INF, NAN, stdClass)
- String utilities with Unicode escape sequences
- Multiple array formats: inline, tabular, list
- Custom delimiters: comma, tab, pipe
- Length validation with strict/lenient modes
- Comprehensive documentation (API reference, format spec)
- PSR-4 autoloading and PSR-12 code style
- PHPUnit test suite (22 tests)
- PHPStan level 8 static analysis
- PHP-CS-Fixer code style checking
- GitHub Actions CI/CD workflows
- Git Flow branching strategy
- Contribution guidelines

### Known Issues
- 2 decoder edge cases with root-level tabular arrays
  - `testDecodeTabularArray` - Parses as object key instead of array
  - `testEncodeDecodeTabularRoundtrip` - Roundtrip fails for root arrays

### Technical Details
- PHP 8.0+ required (uses union types, named arguments, null-safe operators)
- Uses `chr(123)` and `chr(125)` for curly braces to avoid parse errors
- Empty stdClass preserved as object to distinguish from empty array
- Tabular format requires delimiter in header: `[2,]{fields}:`

## [0.1.0] - 2024-11-24

### Added
- Initial development version
- Basic package structure
- Core encoder/decoder prototypes

---

## Version History

- **0.9.0** (2024-11-24) - Current: Beta release with comprehensive features
- **0.1.0** (2024-11-24) - Initial development

## Release Strategy

Following semantic versioning:
- **0.x.x** - Beta versions, API may change
- **1.0.0-rc.x** - Release candidates
- **1.0.0** - First stable release with full spec compliance

[Unreleased]: https://github.com/jimmyahalpara/toon-php/compare/v0.9.0...HEAD
[0.9.0]: https://github.com/jimmyahalpara/toon-php/releases/tag/v0.9.0
[0.1.0]: https://github.com/jimmyahalpara/toon-php/releases/tag/v0.1.0
