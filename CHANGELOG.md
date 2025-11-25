# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Planned
- CLI tool for file conversion
- Performance optimization and benchmarks
- Full TOON specification compliance

## [0.9.0] - 2024-11-25

### Added
- Initial public release
- Complete encoder implementation with tabular array support
- Fully functional decoder with 100% test coverage (22/22 tests passing)
- Type normalization (DateTime, INF, NAN, stdClass)
- String utilities with Unicode escape sequences
- Multiple array formats: inline, tabular, list
- Custom delimiters: comma, tab, pipe
- Length validation with strict/lenient modes
- Comprehensive documentation
  - Enhanced README with 800+ lines (use cases, examples, token savings)
  - Complete API reference (docs/api.md)
  - Detailed format specification (docs/format.md)
  - Documentation index (docs/README.md)
- PSR-4 autoloading and PSR-12 code style
- PHPUnit test suite (22 tests, 39 assertions)
- PHPStan level 8 static analysis (strictest)
- PHP-CS-Fixer code style checking
- GitHub Actions CI/CD workflows
  - Multi-PHP version testing (8.0, 8.1, 8.2, 8.3)
  - Cross-platform testing (Ubuntu, Windows, macOS)
  - Automated code quality checks
  - Coverage reporting
- Git Flow branching strategy (master/develop)
- Comprehensive contribution guidelines (CONTRIBUTING.md)
- CHANGELOG.md for version tracking
- CONTRIBUTORS.md for recognition

### Fixed
- Decoder edge cases with root-level tabular arrays
  - Fixed `isArrayHeader()` regex to match patterns with field definitions
  - Fixed root array detection to handle empty string keys
  - Fixed headerDepth calculation for proper depth matching
- PHPStan level 8 errors
  - Added proper type hints for all array parameters
  - Added non-empty-string type for delimiter parameters
  - Fixed division by zero detection for negative zero
  - Removed unused methods
- PHP-CS-Fixer code style issues
  - Fixed `! empty()` and `! isset()` formatting
  - Fixed arrow function spacing `fn ()`
  - Removed trailing whitespace
- CI/CD coverage generation errors
  - Fixed coverage calculation formula
  - Added clover.xml generation to phpunit.xml
  - Added proper error handling for missing files

### Technical Details
- PHP 8.0+ required (uses union types, named arguments, null-safe operators)
- Uses `chr(123)` and `chr(125)` for curly braces to avoid parse errors
- Empty stdClass preserved as object to distinguish from empty array
- Tabular format requires delimiter in header: `[2,]{fields}:`
- Shaped array types for PHPStan level 8 compliance
- Assertions for delimiter validation in critical paths

## [0.1.0] - 2024-11-24

### Added
- Initial development version
- Basic package structure
- Core encoder/decoder prototypes

---

## Version History

- **0.9.0** (2024-11-25) - Current: Beta release with comprehensive features and 100% test pass rate
- **0.1.0** (2024-11-24) - Initial development

## Release Strategy

Following semantic versioning:
- **0.x.x** - Beta versions, API may change
- **1.0.0-rc.x** - Release candidates
- **1.0.0** - First stable release with full spec compliance

[Unreleased]: https://github.com/jimmyahalpara/toon-php/compare/v0.9.0...HEAD
[0.9.0]: https://github.com/jimmyahalpara/toon-php/releases/tag/v0.9.0
[0.1.0]: https://github.com/jimmyahalpara/toon-php/releases/tag/v0.1.0
