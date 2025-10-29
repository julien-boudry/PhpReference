
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

## Quick Start

### Installation

Install PhpReference via Composer:

```bash
composer require --dev julien-boudry/php-reference
```

### Basic Usage

Generate documentation for a namespace:

```bash
php vendor/bin/php-reference MyNamespace\\MyProject
```

This will analyze all classes in `MyNamespace\MyProject` and generate Markdown documentation in the `./output` directory.

> **Note:** If you're working on the PhpReference project itself, use `php bin/php-reference` instead of `php vendor/bin/php-reference`.

### Generate with Output Directory

Specify a custom output directory:

```bash
php vendor/bin/php-reference MyNamespace\\MyProject --output=./docs/api
# Or with shorthand:
php vendor/bin/php-reference MyNamespace\\MyProject -o ./docs/api
```

### Include All Public Elements

By default, only elements marked with `@api` are documented. To include all public elements:

```bash
php vendor/bin/php-reference MyNamespace\\MyProject --api=public
```

### Append Mode

By default, PhpReference cleans the output directory before generation. To append without cleaning:

```bash
php vendor/bin/php-reference MyNamespace\\MyProject --append
# Or with shorthand:
php vendor/bin/php-reference MyNamespace\\MyProject -a
```

### Add Source Links

Link documentation to your source code repository:

```bash
php vendor/bin/php-reference MyNamespace\\MyProject --source-url-base=https://github.com/user/repo/blob/main
```

### Combine Multiple Options

You can combine any options:

```bash
php vendor/bin/php-reference MyNamespace\\MyProject \
  --output=./docs/api \
  --api=public \
  --append \
  --index-file-name=API \
  --source-url-base=https://github.com/user/repo/blob/main
```

## Command-Line Options

| Option | Shortcut | Description | Example |
|--------|----------|-------------|---------|
| `namespace` | - | Namespace to analyze (optional if set in config) | `MyNamespace\\MyProject` |
| `--output` | `-o` | Output directory | `--output=./docs/api` |
| `--append` | `-a` | Do not clean output directory before generation | `--append` |
| `--api` | - | API definition to use (`api`, `public`, `beta`) | `--api=public` |
| `--index-file-name` | - | Name of the index file (without extension) | `--index-file-name=index` |
| `--source-url-base` | - | Base URL for source code links | `--source-url-base=https://github.com/user/repo/blob/main` |
| `--config` | `-c` | Path to configuration file | `--config=./my-config.php` |

## Configuration File

For repeated usage, create a `reference.php` configuration file at your project root to avoid typing arguments every time.

📖 **[Full Configuration Documentation](docs/CONFIGURATION_FILE.md)**

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

Use `--api=public` to document all public classes, methods, and properties regardless of `@api` tags:

```bash
php vendor/bin/php-reference MyNamespace\\MyProject --api=public
```

### Available API Definitions

- **`api`** (default): Only elements marked with `@api` tag
- **`public`**: All public elements
- **`beta`**: Elements marked with `@beta` tag

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
