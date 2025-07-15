# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-01-15

### Added
- **Modern PHP 8.3+ support** with strict typing and modern language features
- **Comprehensive test suite** with PHPUnit 11+ (59 tests, 356 assertions)
- **PHPStan level 8 compliance** for maximum type safety
- **GitHub Pages documentation** with complete API reference and examples
- **Modern CI/CD pipeline** with GitHub Actions for PHP 8.3+ and 8.4
- **Automated GitHub Pages deployment** for documentation
- **Professional badges** in README for CI status, PHPStan level, PHP version, license, and Packagist
- **Enhanced error handling** with proper exception types and messages
- **Method chaining support** for fluent interface design
- **Comprehensive documentation** including:
  - Installation guide with composer
  - Basic usage examples for all crop strategies
  - Advanced usage patterns and integration examples
  - Complete API reference with method signatures
  - Strategy explanations and performance comparisons

### Changed
- **BREAKING**: Minimum PHP version increased from 5.4 to 8.3+
- **BREAKING**: Namespace changed from `stojg\crop` to `drzippie\crop`
- **BREAKING**: Removed face detection functionality (CropFace class and haar package)
- **BREAKING**: Removed GD extension dependency, now uses ImageMagick only
- **Updated composer.json** with modern structure and dependencies
- **Enhanced description** from "Image cropping classes" to "Modern PHP library for intelligent image cropping with multiple algorithms"
- **Improved license** specification to BSD-2-Clause
- **Updated project metadata** for maintained fork status

### Removed
- **Face detection support** (CropFace class and haar package) due to memory issues
- **GD extension dependency** - now uses ImageMagick exclusively
- **PHP CodeSniffer dependency** - replaced with PHPStan for better type checking
- **Legacy build tools** - modernized with composer scripts
- **Outdated test fixtures** and deprecated test patterns

### Fixed
- **117 PHPStan errors** resolved for level 8 compliance
- **Null safety issues** in Crop::resizeAndCrop method
- **Variable shadowing** in CropEntropy::colorEntropy method
- **Test isolation issues** preventing reliable test execution
- **Memory management** by removing resource-intensive face detection
- **Type safety** with proper type hints throughout codebase
- **Floating point precision** in tests using assertEqualsWithDelta

### Security
- **Removed sensitive dependencies** that could expose security vulnerabilities
- **Enhanced type safety** prevents common PHP pitfalls
- **Strict typing** throughout codebase reduces runtime errors

### Performance
- **Significant memory reduction** by removing face detection algorithms
- **Improved processing speed** with optimized algorithms
- **Better resource management** with proper cleanup patterns

### Documentation
- **Complete API reference** with method signatures and examples
- **Usage examples** for basic and advanced scenarios
- **Strategy explanations** for choosing the right cropping algorithm
- **Performance comparisons** between different strategies
- **Integration examples** for Laravel, Symfony, and other frameworks
- **Testing documentation** with helper classes and patterns

---

## Original Fork History

This project is a maintained fork of [stojg/crop](https://github.com/stojg/crop) which was archived on April 30, 2021. The original project included:

### Original Features (pre-fork)
- Basic image cropping with three strategies: CropCenter, CropEntropy, CropBalanced
- Face detection with PHP facedetect extension
- Support for PHP 5.4+ with basic ImageMagick integration
- Simple test suite with basic functionality

### Fork Motivation
The original project was archived without maintenance, but the core cropping algorithms remained valuable for modern PHP applications. This fork modernizes the codebase while preserving the intelligent cropping capabilities that made the original library useful.

### Key Improvements Over Original
1. **Modern PHP compatibility** - Updated from PHP 5.4 to 8.3+ with strict typing
2. **Removed problematic dependencies** - Face detection caused memory issues in production
3. **Enhanced reliability** - Comprehensive test suite with proper error handling
4. **Better developer experience** - PHPStan compliance, modern tooling, complete documentation
5. **Active maintenance** - Regular updates, bug fixes, and security improvements

[1.0.0]: https://github.com/drzippie/crop/releases/tag/v1.0.0