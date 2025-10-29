# Configuration

PhpReference supports a PHP configuration file to avoid typing command-line arguments every time.

> **Note:** In this documentation, commands use `php bin/php-reference` (for development on PhpReference itself). If you installed PhpReference via Composer in your project, use `php vendor/bin/php-reference` instead.

## Configuration File

Create a `reference.php` file at the root of your project:

```php
<?php

use JulienBoudry\PhpReference\Definition\HasTagApi;
use JulienBoudry\PhpReference\Definition\IsPubliclyAccessible;

return [
    // The namespace for which to generate documentation
    'namespace' => 'MonNamespace\\MonProjet',

    // Output directory for the generated documentation
    'output' => __DIR__ . '/docs',

    // Do not clean the output directory before generation (default: false)
    'append' => false,

    // Public API definition - can be:
    // - A string: 'api' (default), 'public', or 'beta'
    // - An instance of a class implementing PublicApiDefinitionInterface
    'api' => 'api', // or new HasTagApi()

    // The name of the index file to generate (without extension, default: 'readme')
    'index-file-name' => 'readme',

    // Base URL for source code links
    // Example: https://github.com/user/repo/blob/main
    // The file path will be automatically appended (e.g., /src/MyClass.php)
    // If not set, no source links will be generated
    'source-url-base' => 'https://github.com/user/repo/blob/main',

    // Disable interactive mode - no prompts will be shown (default: false)
    'no-interaction' => true,
];
```

### Configuration Options Explained

#### `namespace` (string, required)
The PHP namespace to analyze and document. All classes within this namespace will be discovered and processed.

#### `output` (string, default: `./output`)
The directory where documentation files will be generated. The directory must exist before running the command.

#### `append` (bool, default: `false`)
When `false`, the output directory is cleaned before generation. When `true`, new files are added and existing files are overwritten, but other files are preserved.

#### `api` (string|object, default: `'api'`)
Defines which elements are included in the documentation:
- String values: `'api'`, `'public'`, `'beta'`
- Object instances: `new HasTagApi()`, `new IsPubliclyAccessible()`

#### `index-file-name` (string, default: `'readme'`)
Name of the main index file (without extension). The `.md` extension is added automatically.

#### `source-url-base` (string, optional)
Base URL for linking to source code. If provided, each documented element will include a link to its source file.
Example: `https://github.com/username/repository/blob/main`

#### `no-interaction` (bool, default: `false`)
When `true`, the command runs without any prompts. Useful for CI/CD environments or automated workflows.

## Available API Definitions

### Via string (CLI and config)

When using the `--api` option on the command line or the `'api'` key in the configuration file, you can use these string values:

- **`api`** (default): Includes only elements marked with the `@api` PHPDoc tag
- **`public`**: Includes all public elements (classes, methods, properties, constants) regardless of tags
- **`beta`**: Includes elements marked with the `@beta` PHPDoc tag

**Command-line examples:**
```bash
php bin/php-reference --api=api      # Default behavior
php bin/php-reference --api=public   # Include all public elements
php bin/php-reference --api=beta     # Include @beta tagged elements
```

**Config file example:**
```php
return [
    'api' => 'public', // String value
    // ...
];
```

### Via object (config only)

In the configuration file, you can also use class instances for more control:

```php
use JulienBoudry\PhpReference\Definition\HasTagApi;
use JulienBoudry\PhpReference\Definition\IsPubliclyAccessible;

// Includes only elements with @api
'api' => new HasTagApi(),

// Includes all public elements
'api' => new IsPubliclyAccessible(),
```

## Create a Custom Definition

```php
<?php

use JulienBoudry\PhpReference\Definition\Base;
use JulienBoudry\PhpReference\Definition\PublicApiDefinitionInterface;
use JulienBoudry\PhpReference\Reflect\ReflectionWrapper;

class MyCustomDefinition extends Base implements PublicApiDefinitionInterface
{
    public function isPartOfPublicApi(ReflectionWrapper $reflectionWrapper): bool
    {
        if (!$this->baseExclusion($reflectionWrapper)) {
            return false;
        }

        // Your custom logic here
        return $reflectionWrapper->hasTagCustom; // For example
    }
}

// In your config
return [
    'api' => new MyCustomDefinition(),
    // ...
];
```

## Argument Priority

The priority is as follows (from highest to lowest):

1. **Command-line arguments** (absolute priority)
2. **Configuration file**
3. **Default values**

## Usage Examples

### Basic usage with configuration file
```bash
# Uses all settings from reference.php
php bin/php-reference generate:documentation
```

### Override the namespace
```bash
# Overrides the namespace from config file
php bin/php-reference generate:documentation MyNamespace\\MyProject
```

### Change the API definition
```bash
# Include all public elements instead of only @api tagged
php bin/php-reference --api=public

# Include @beta tagged elements
php bin/php-reference --api=beta
```

### Change output directory
```bash
# Override output directory
php bin/php-reference --output=/tmp/docs
php bin/php-reference -o ./public/api-docs
```

### Use append mode
```bash
# Don't clean the output directory first
php bin/php-reference --append
php bin/php-reference -a
```

### Custom index file name
```bash
# Generate API.md instead of readme.md
php bin/php-reference --index-file-name=API
```

### Add source code links
```bash
# Add links to GitHub source files
php bin/php-reference --source-url-base=https://github.com/user/repo/blob/main
```

### Use an alternative configuration file
```bash
# Use a custom config file
php bin/php-reference --config=/path/to/config.php
php bin/php-reference -c ./configs/api-config.php
```

### Combine multiple options
```bash
# Full example with all options
php bin/php-reference MyNamespace\\MyProject \
  --output=./docs/api \
  --api=public \
  --append \
  --index-file-name=API-Reference \
  --source-url-base=https://github.com/user/repo/blob/main
```

## Example Configuration Files

### Basic configuration
```php
<?php
return [
    'namespace' => 'App\\',
    'output' => getcwd() . '/api-docs',
    'append' => false,
    'api' => 'api', // Uses HasTagApi (can also use 'public' or 'beta')
    'index-file-name' => 'readme', // Default: 'readme'
    'source-url-base' => 'https://github.com/myuser/myrepo/blob/main',
    'no-interaction' => true,
];
```

### Advanced configuration
```php
<?php

use JulienBoudry\PhpReference\Definition\IsPubliclyAccessible;

return [
    'namespace' => 'MyLibrary\\',
    'output' => __DIR__ . '/public-api-docs',
    'append' => true,
    'api' => new IsPubliclyAccessible(), // Direct instance
    'index-file-name' => 'API-INDEX',
    'source-url-base' => 'https://github.com/myuser/mylibrary/blob/develop',
    'no-interaction' => true, // Run without prompts
];
```

## Available Options Summary

| Option | Shortcut | Description | Config example | CLI example |
|--------|----------|-------------|----------------|-------------|
| `namespace` | - | Namespace to analyze | `'namespace' => 'Mon\\Namespace'` | `MonNamespace` |
| `output` | `-o` | Output directory | `'output' => '/path/to/docs'` | `--output=/path/to/docs` |
| `append` | `-a` | Do not clean before generation | `'append' => true` | `--append` |
| `api` | - | API definition to use | `'api' => 'api'` or `'api' => new HasTagApi()` | `--api=public` |
| `index-file-name` | - | Name of the index file (without extension) | `'index-file-name' => 'readme'` | `--index-file-name=index` |
| `source-url-base` | - | Base URL for source links | `'source-url-base' => 'https://github.com/user/repo/blob/main'` | `--source-url-base=https://github.com/user/repo/blob/main` |
| `no-interaction` | - | Disable interactive mode | `'no-interaction' => true` | (Not available via CLI) |
| `config` | `-c` | Configuration file path | (Not available in config file) | `--config=/custom/path.php` |
