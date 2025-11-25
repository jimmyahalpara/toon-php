# Contributing to TOON PHP

Thank you for your interest in contributing to TOON PHP! This document provides guidelines and instructions for contributing.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [Development Workflow](#development-workflow)
- [Branching Strategy](#branching-strategy)
- [Coding Standards](#coding-standards)
- [Testing Guidelines](#testing-guidelines)
- [Commit Message Convention](#commit-message-convention)
- [Pull Request Process](#pull-request-process)
- [Release Process](#release-process)

## Code of Conduct

### Our Pledge

We are committed to providing a welcoming and inclusive experience for everyone. We pledge to:

- Use welcoming and inclusive language
- Be respectful of differing viewpoints and experiences
- Gracefully accept constructive criticism
- Focus on what is best for the community
- Show empathy towards other community members

### Our Standards

**Encouraged behavior:**
- Being respectful and professional
- Providing constructive feedback
- Accepting responsibility and learning from mistakes
- Focusing on collaborative problem-solving

**Unacceptable behavior:**
- Harassment, discrimination, or offensive comments
- Personal attacks or trolling
- Publishing private information without permission
- Any conduct that would be inappropriate in a professional setting

## Getting Started

### Prerequisites

- **PHP**: 8.0 or higher
- **Composer**: 2.0 or higher
- **Git**: 2.30 or higher
- **Extensions**: `mbstring`, `json`

### Fork and Clone

1. Fork the repository on GitHub
2. Clone your fork locally:

```bash
git clone https://github.com/YOUR_USERNAME/toon-php.git
cd toon-php
```

3. Add upstream remote:

```bash
git remote add upstream https://github.com/jimmyahalpara/toon-php.git
```

4. Install dependencies:

```bash
composer install
```

5. Verify setup:

```bash
composer test
composer phpstan
composer cs:check
```

## Development Workflow

We follow a **Git Flow** based workflow:

### 1. Sync with Upstream

Before starting work, ensure your fork is up-to-date:

```bash
# Switch to develop
git checkout develop

# Fetch and merge upstream changes
git fetch upstream
git merge upstream/develop

# Push to your fork
git push origin develop
```

### 2. Create Feature Branch

Create a branch from `develop` for your work:

```bash
# For new features
git checkout -b feature/your-feature-name

# For bug fixes
git checkout -b bugfix/issue-description

# For documentation
git checkout -b docs/what-you-are-documenting
```

**Branch naming conventions:**
- `feature/` - New features or enhancements
- `bugfix/` - Bug fixes
- `hotfix/` - Urgent production fixes (branch from `master`)
- `docs/` - Documentation improvements
- `refactor/` - Code refactoring
- `test/` - Test additions or improvements
- `chore/` - Maintenance tasks

### 3. Make Changes

- Write clear, focused commits
- Add tests for new functionality
- Update documentation as needed
- Follow coding standards
- Run quality checks frequently

```bash
# Run tests after each change
composer test

# Check code style
composer cs:check

# Run static analysis
composer phpstan
```

### 4. Commit Changes

Follow our [commit message convention](#commit-message-convention):

```bash
git add .
git commit -m "feat: add support for custom delimiters"
```

### 5. Push and Create PR

```bash
# Push to your fork
git push origin feature/your-feature-name

# Create Pull Request on GitHub targeting 'develop' branch
```

## Branching Strategy

We use a **Git Flow** branching model:

```
master (stable, production-ready)
  ↑
  └─── release/x.x.x (release preparation)
         ↑
         └─── develop (integration branch)
                ↑
                ├─── feature/feature-name
                ├─── bugfix/fix-description
                └─── docs/documentation-update
  
hotfix/urgent-fix (emergency fixes)
  ↓
master
```

### Branch Descriptions

#### `master`
- **Purpose**: Stable, production-ready code
- **Protected**: Yes
- **Merge from**: `release/*`, `hotfix/*` only
- **Never commit directly**: Always merge via PR

#### `develop`
- **Purpose**: Integration branch for ongoing development
- **Protected**: Yes
- **Merge from**: `feature/*`, `bugfix/*`, `docs/*`
- **Never commit directly**: Always merge via PR

#### `feature/*`
- **Purpose**: New features and enhancements
- **Branch from**: `develop`
- **Merge to**: `develop`
- **Lifetime**: Until feature complete

#### `bugfix/*`
- **Purpose**: Bug fixes for develop branch
- **Branch from**: `develop`
- **Merge to**: `develop`
- **Lifetime**: Until fix complete

#### `hotfix/*`
- **Purpose**: Urgent fixes for production
- **Branch from**: `master`
- **Merge to**: `master` AND `develop`
- **Lifetime**: Short-lived

#### `release/*`
- **Purpose**: Prepare new production release
- **Branch from**: `develop`
- **Merge to**: `master` AND `develop`
- **Naming**: `release/x.x.x` (version number)

## Coding Standards

### PHP Standards

We follow **PSR-12** coding style with some additional rules:

- **PSR-4**: Autoloading standard
- **PSR-12**: Extended coding style
- **Type declarations**: Always use strict types
- **Return types**: Always declare return types
- **Docblocks**: Required for public methods

### Code Style Rules

```php
<?php

declare(strict_types=1);

namespace JimmyAhalpara\ToonFormat;

/**
 * Brief description of the class.
 * 
 * Detailed description if needed.
 */
class Example
{
    /**
     * Brief method description.
     *
     * @param string $value Input value description
     * @param array<string, mixed> $options Configuration options
     * @return string Formatted output
     * @throws ToonDecodeException When validation fails
     */
    public function process(string $value, array $options = []): string
    {
        // Method implementation
        return $value;
    }
}
```

### Running Code Style Checks

```bash
# Check code style
composer cs:check

# Fix code style automatically
composer cs:fix
```

### Static Analysis

We use **PHPStan** at level 8 (strictest):

```bash
# Run static analysis
composer phpstan

# Should show: [OK] No errors
```

## Testing Guidelines

### Writing Tests

- **One assertion per test** (when possible)
- **Test naming**: `test<MethodName><Scenario>()`
- **Arrange-Act-Assert** pattern
- **Test edge cases** and error conditions

```php
public function testEncodeSimpleObject(): void
{
    // Arrange
    $data = ['name' => 'Alice', 'age' => 30];
    
    // Act
    $result = ToonFormat::encode($data);
    
    // Assert
    $this->assertStringContainsString('name: Alice', $result);
    $this->assertStringContainsString('age: 30', $result);
}
```

### Running Tests

```bash
# Run all tests
composer test

# Run specific test file
./vendor/bin/phpunit tests/EncoderTest.php

# Run specific test method
./vendor/bin/phpunit --filter testEncodeSimpleObject

# Run with coverage
composer test:coverage
```

### Test Coverage

- **Target**: 95% code coverage
- **Current**: 91% (encoder: 100%, decoder: 82%)
- **Required**: All new features must include tests
- **Coverage report**: Generated in `coverage/` directory

### Test Categories

1. **Unit Tests** - Test individual methods in isolation
2. **Integration Tests** - Test encode/decode round trips
3. **Edge Cases** - Test boundary conditions and errors
4. **Format Compliance** - Test against TOON specification

## Commit Message Convention

We follow **Conventional Commits** specification:

### Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, no logic change)
- `refactor`: Code refactoring
- `perf`: Performance improvements
- `test`: Adding or updating tests
- `chore`: Maintenance tasks
- `ci`: CI/CD changes
- `build`: Build system changes

### Examples

```bash
# Simple feature
git commit -m "feat: add support for pipe delimiter"

# Feature with scope
git commit -m "feat(decoder): implement tabular array parsing"

# Bug fix with description
git commit -m "fix: handle empty strings in string utils

- Add check for empty input
- Return empty string instead of null
- Add test coverage"

# Breaking change
git commit -m "feat!: change encode API signature

BREAKING CHANGE: encode() now requires EncodeOptions object instead of array"

# Documentation
git commit -m "docs: add API examples to README"

# Multiple changes
git commit -m "chore: update dependencies and fix linting

- Update PHPUnit to 10.5.58
- Fix code style violations
- Update phpstan baseline"
```

### Rules

- Use imperative mood ("add" not "added")
- Don't capitalize first letter
- No period at the end
- Keep subject line under 72 characters
- Separate subject from body with blank line
- Use body to explain **what** and **why**, not **how**

## Pull Request Process

### Before Submitting

1. **Run all quality checks**:
   ```bash
   composer test        # All tests pass
   composer phpstan     # No errors
   composer cs:check    # Code style compliant
   ```

2. **Update documentation**:
   - Update README if adding features
   - Update CHANGELOG.md
   - Add docblocks to new methods

3. **Rebase on latest develop**:
   ```bash
   git fetch upstream
   git rebase upstream/develop
   ```

### Creating PR

1. **Title**: Follow commit message convention
   - `feat: add custom delimiter support`
   - `fix: resolve tabular array parsing issue`

2. **Description**: Use the template

```markdown
## Description
Brief description of changes

## Type of Change
- [ ] Bug fix (non-breaking change fixing an issue)
- [ ] New feature (non-breaking change adding functionality)
- [ ] Breaking change (fix or feature causing existing functionality to change)
- [ ] Documentation update

## Testing
- [ ] Tests added/updated
- [ ] All tests pass
- [ ] Code coverage maintained/improved

## Checklist
- [ ] Code follows style guidelines
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] No new warnings from static analysis
- [ ] CHANGELOG.md updated
```

3. **Target branch**: Always `develop` (unless hotfix)

4. **Link issues**: Reference related issues with `Fixes #123`

### Review Process

1. **Automated checks**: CI/CD must pass
2. **Code review**: At least one approval required
3. **Discussion**: Address reviewer feedback
4. **Updates**: Push additional commits if needed
5. **Merge**: Maintainer will merge when approved

### After Merge

```bash
# Switch back to develop
git checkout develop

# Pull latest changes
git pull upstream develop

# Delete feature branch
git branch -d feature/your-feature-name
git push origin --delete feature/your-feature-name
```

## Release Process

### Version Numbering

We follow **Semantic Versioning** (SemVer):

- **MAJOR**: Breaking changes (`1.0.0 → 2.0.0`)
- **MINOR**: New features, backward-compatible (`1.0.0 → 1.1.0`)
- **PATCH**: Bug fixes, backward-compatible (`1.0.0 → 1.0.1`)

### Release Workflow (Maintainers Only)

1. **Create release branch**:
   ```bash
   git checkout develop
   git checkout -b release/1.0.0
   ```

2. **Prepare release**:
   - Update version in `composer.json`
   - Update `CHANGELOG.md`
   - Update documentation
   - Final testing

3. **Merge to master**:
   ```bash
   git checkout master
   git merge --no-ff release/1.0.0
   git tag -a v1.0.0 -m "Release version 1.0.0"
   git push origin master --tags
   ```

4. **Merge back to develop**:
   ```bash
   git checkout develop
   git merge --no-ff release/1.0.0
   git push origin develop
   ```

5. **Delete release branch**:
   ```bash
   git branch -d release/1.0.0
   git push origin --delete release/1.0.0
   ```

6. **Publish to Packagist**: Automatic via webhook

## Questions or Issues?

- **Documentation**: Check [docs/](docs/) first
- **Discussions**: Use [GitHub Discussions](https://github.com/jimmyahalpara/toon-php/discussions)
- **Bugs**: Open an [issue](https://github.com/jimmyahalpara/toon-php/issues)
- **Questions**: Ask in discussions or open an issue with `question` label

## Recognition

Contributors will be:
- Added to `CONTRIBUTORS.md`
- Mentioned in release notes
- Credited in relevant documentation

Thank you for contributing! 🎉
