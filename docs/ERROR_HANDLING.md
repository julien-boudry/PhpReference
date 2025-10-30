# Error Handling System

## Overview

PhpReference uses a centralized error collection system that allows non-fatal issues to be tracked without stopping the documentation generation process.

## Architecture

### Error Collection

The `ErrorCollector` class collects errors, warnings, and notices during execution:

```php
// In Execution class
public readonly ErrorCollector $errorCollector;

// Collecting an error
Execution::$instance->errorCollector->addWarning(
    message: 'Failed to resolve reference',
    context: 'Processing @see tag',
    element: $reflectionWrapper,
);
```

### Error Levels

Three severity levels are available via the `ErrorLevel` enum:

- **NOTICE** 📝 - Informational messages
- **WARNING** ⚠️  - Issues that don't prevent generation but should be reviewed
- **ERROR** ❌ - Serious issues that may affect documentation quality

### Custom Exceptions

Specific exceptions for different error scenarios:

- `PhpReferenceException` - Base exception for all project exceptions
- `UnresolvableReferenceException` - When a class/method/property reference cannot be found
- `UnsupportedOperationException` - When an operation is called on an incompatible wrapper
- `InvalidConfigurationException` - Configuration issues
- `PhpDocParsingException` - PHPDoc parsing issues (existing)

## Usage Examples

### Adding Errors

```php
// Simple warning
$errorCollector->addWarning('Missing return type');

// Warning with context
$errorCollector->addWarning(
    message: 'Failed to resolve @see tag',
    context: 'ClassName::methodName',
);

// Warning with element reference
$errorCollector->addWarning(
    message: 'Invalid PHPDoc format',
    context: 'Processing method documentation',
    element: $methodWrapper,
);

// Error with exception
$errorCollector->addError(
    message: 'Cannot process class',
    level: ErrorLevel::ERROR,
    exception: $thrownException,
);
```

### Retrieving Errors

```php
// Get all errors
$allErrors = $errorCollector->getErrors();

// Get only warnings
$warnings = $errorCollector->getErrors(ErrorLevel::WARNING);

// Check if there are errors
if ($errorCollector->hasErrors(ErrorLevel::ERROR)) {
    // Handle errors
}

// Get count
$warningCount = $errorCollector->getErrorCount(ErrorLevel::WARNING);

// Get summary
$summary = $errorCollector->getSummary();
// Returns: ['warning' => 5, 'error' => 2]
```

### Displaying Error Report

The error collector can format errors for console output:

```php
echo $errorCollector->formatForConsole();
```

Output example:
```
=== Error Report ===

⚠️  WARNINGS (3):
--------------------------------------------------
  [14:23:15] [MyClass::myMethod] Failed to resolve @see reference
    Context: Processing @see tag
  [14:23:16] Missing return type documentation
  ...

❌ ERRORS (1):
--------------------------------------------------
  [14:23:20] Cannot generate documentation
    Exception: Class not found
```

## Migration from Old System

The old `addWarning(PhpDocParsingException)` method is deprecated but still functional during the transition period:

```php
// Old way (deprecated)
Execution::$instance->addWarning($exception);

// New way
Execution::$instance->errorCollector->addWarning(
    message: $exception->getMessage(),
    context: 'PHPDoc Parsing',
);
```

## Best Practices

### When to Use Each Level

- **NOTICE**: Informational messages (e.g., "Using default value")
- **WARNING**: Issues that should be reviewed but don't prevent generation (e.g., "Missing @param tag")
- **ERROR**: Serious issues that may affect documentation quality (e.g., "Cannot resolve class reference")

### When to Throw vs Collect

- **Throw exception**: Fatal errors that prevent continuing (e.g., invalid configuration, missing required data)
- **Collect error**: Non-fatal issues that should be reported but allow generation to continue (e.g., broken @see link, missing documentation)

### Exception Types

Use specific exceptions instead of generic ones:

```php
// ❌ Bad
throw new \LogicException('Class not found');

// ✅ Good
throw new UnresolvableReferenceException(
    reference: $className,
    message: 'Class not found in indexed namespace',
);
```

## Command Line Integration

The `GenerateDocumentationCommand` automatically displays an error report after generation:

```bash
# Normal output - shows summary only
php bin/php-reference

# Verbose output - shows detailed error report
php bin/php-reference -v
```

## Future Improvements

Potential enhancements:

1. **Error filtering**: Filter errors by context or element type
2. **JSON export**: Export errors for CI/CD integration
3. **Error thresholds**: Fail build if error count exceeds threshold
4. **Categorization**: Group errors by category (missing docs, broken links, etc.)
5. **Auto-fix suggestions**: Provide suggestions for common issues
