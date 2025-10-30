
# PhpReference
> **Main Author:** [Julien Boudry](https://www.linkedin.com/in/julienboudry/)  
> **License:** [BSD-3-Clause](LICENSE) - Please [say hello](https://github.com/julien-boudry/PhpReference/discussions) if you like or use this code!  
> **Donation:** ₿ [bc1q3jllk3qd9fjvvuqy07tawkv7t6h7qjf55fc2gh](https://blockchair.com/bitcoin/address/bc1q3jllk3qd9fjvvuqy07tawkv7t6h7qjf55fc2gh) or [GitHub Sponsor Page](https://github.com/sponsors/julien-boudry)

[![License](https://img.shields.io/badge/License-BSD_3--Clause-blue.svg)](https://opensource.org/licenses/BSD-3-Clause)
[![Packagist](https://img.shields.io/packagist/v/julien-boudry/php-reference.svg)](https://packagist.org/packages/julien-boudry/php-reference)

**PhpReference** is a powerful documentation generator for PHP projects. It analyzes your codebase using reflection and generates comprehensive Markdown documentation for your namespaces, classes, methods, properties, and constants.

## Workflow

- **Automatic Documentation Generation** - Analyzes PHP namespaces using reflection to extract classes, methods, properties, constants, and their metadata
- **PHPDoc Integration** - Parses and renders PHPDoc blocks including descriptions, parameters, return types, and custom tags
- **Cross-Referencing** - Generates internal links between classes, methods, and properties
- **Public API Focus** - Control what gets documented using `@api` tags or visibility rules
- **Markdown Output** - Clean, readable documentation in Markdown format ready for GitHub, GitLab, or your documentation site

## Features

- **Full Reflection Analysis** - Classes, interfaces, traits, enums, methods, properties, constants
- **PHPDoc Parsing** - Descriptions, `@param`, `@return`, `@throws`, `@see`, custom tags
- **Type Resolution** - Automatic linking to documented types
- **Cross-References** - Internal links between elements
- **Inheritance Tracking** - Shows declaring class for inherited members
- **Custom Public API Rules** - Define what gets documented
- **Source Links** - Optional links back to source code
- **Configuration File Support** - Set your preferences once in `reference.php` and run without arguments

## Quick Start

### Installation

Install PhpReference via Composer:

```bash
composer require --dev julien-boudry/php-reference
```

### Recommended: Using a Configuration File

**The recommended way to use PhpReference is with a configuration file.** This avoids typing the same arguments repeatedly and makes your documentation setup reproducible.

Create a `reference.php` file at your project root:

```php
<?php

return [
    'namespace' => 'MyNamespace\\MyProject',
    'output' => __DIR__ . '/docs/api',
    'api' => 'HasTagApi', // 'HasTagApi' (default) or 'IsPubliclyAccessible'
    'index-file-name' => 'readme',
    'source-url-base' => 'https://github.com/username/repository/blob/main',
];
```

Then simply run:

```bash
php vendor/bin/php-reference
```

📖 **[Full Configuration Documentation](docs/CONFIGURATION_FILE.md)** - Learn about all available options, priority rules, and advanced usage.

> **Note:** If you're working on the PhpReference project itself, use `php bin/php-reference` instead of `php vendor/bin/php-reference`.

### Quick Start Without Configuration

For a quick one-time generation, you can use command-line arguments:

```bash
# Basic usage - generate docs for a namespace
php vendor/bin/php-reference MyNamespace\\MyProject

# With custom output directory
php vendor/bin/php-reference MyNamespace\\MyProject --output=./docs/api

# Include all public elements (not just @api tagged)
php vendor/bin/php-reference MyNamespace\\MyProject --api=IsPubliclyAccessible

# Append mode (don't clean output directory first)
php vendor/bin/php-reference MyNamespace\\MyProject --append

# With source code links
php vendor/bin/php-reference MyNamespace\\MyProject --source-url-base=https://github.com/user/repo/blob/main
```

> **💡 Tip:** While command-line arguments work well for testing, using a configuration file is recommended for regular use and CI/CD pipelines.

## Command-Line Options

| Option | Shortcut | Description | Example |
|--------|----------|-------------|---------|
| `namespace` | - | Namespace to analyze (optional if set in config) | `MyNamespace\\MyProject` |
| `--output` | `-o` | Output directory | `--output=./docs/api` |
| `--append` | `-a` | Do not clean output directory before generation | `--append` |
| `--api` | - | API definition to use (`HasTagApi`, `IsPubliclyAccessible`) | `--api=IsPubliclyAccessible` |
| `--index-file-name` | - | Name of the index file (without extension) | `--index-file-name=index` |
| `--source-url-base` | - | Base URL for source code links | `--source-url-base=https://github.com/user/repo/blob/main` |
| `--config` | `-c` | Path to configuration file | `--config=./my-config.php` |

**💡 Remember:** Command-line arguments override configuration file settings. For regular use, prefer using a configuration file and only override specific options when needed.

## Common Use Cases

### Development Workflow

Once you have a `reference.php` configuration file, your workflow becomes simple:

```bash
# Generate documentation (uses all config settings)
php vendor/bin/php-reference

# Override only what you need for a specific run
php vendor/bin/php-reference --api=IsPubliclyAccessible  # Temporarily include all public elements
php vendor/bin/php-reference --append                     # Don't clean output this time
```

### CI/CD Integration

Using a configuration file makes CI/CD integration straightforward:

```yaml
# .github/workflows/docs.yml
- name: Generate API Documentation
  run: php vendor/bin/php-reference
```

No need to specify arguments in your workflow file - everything is configured in `reference.php`.

### Multiple Documentation Targets

Generate different documentation sets using different config files:

```bash
# Public API documentation (with @api tags)
php vendor/bin/php-reference --config=reference-public.php

# Complete documentation (all public elements)
php vendor/bin/php-reference --config=reference-complete.php
```

## Public API Control

PhpReference lets you control what gets documented:

### Using `@api` Tags (Default)

Mark elements for documentation with the `@api` PHPDoc tag:

```php
/**
 * This class will be documented.
 * @api
 */
class MyClass
{
    /**
     * This method will be documented.
     * @api
     */
    public function myMethod(): void
    {
    }
    
    // This method will NOT be documented (no @api tag)
    public function internalMethod(): void
    {
    }
}
```

### Include All Public Elements

Use `--api=IsPubliclyAccessible` to document all public classes, methods, and properties regardless of `@api` tags:

```bash
php vendor/bin/php-reference MyNamespace\\MyProject --api=IsPubliclyAccessible
```

### Available API Definitions

- **`HasTagApi`** (default): Only elements marked with `@api` tag
- **`IsPubliclyAccessible`**: All public elements

## Output Structure

PhpReference generates a structured documentation hierarchy:

```
output/
├── README.md                          # API summary with all documented classes
└── ref/
    └── MyNamespace/                   # First namespace level
        └── SubNamespace/              # Second namespace level
            ├── MyClass/               # Directory for MyClass
            │   ├── class_MyClass.md        # Class documentation
            │   ├── method_myMethod.md      # Method documentation
            │   └── property_myProperty.md  # Property documentation
            └── AnotherClass/          # Directory for another class
                └── class_AnotherClass.md   # Another class documentation
```

## Requirements

- PHP 8.4 or higher
- Composer for dependency management

## Contributing

Contributions are welcome! See [CONTRIBUTING.md](CONTRIBUTING.md) for development setup and guidelines.

## Credits

Created and maintained by [Julien Boudry](https://www.linkedin.com/in/julienboudry/).

If you find this project useful, please consider:
- ⭐ Starring the repository
- 💬 [Sharing your use case](https://github.com/julien-boudry/PhpReference/discussions)
- 💝 [Sponsoring the project](https://github.com/sponsors/julien-boudry)
