# Configuration

PhpReference supports a PHP configuration file to avoid typing command-line arguments every time.

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

    // Do not clean the output directory before generation
    'append' => false,

    // Public API definition - can be:
    // - An instance of a class implementing PublicApiDefinitionInterface
    // - A string corresponding to a registered definition ('api', 'public')
    'api' => new HasTagApi(), // or 'api' as a string
];
```

## Available API Definitions

### Via string (CLI and config)

- **`api`**: Includes elements marked with `@api` (default)
- **`public`**: Includes all public elements
- **`beta`**: Includes elements marked with `@beta`

### Via object (config only)

```php
use JulienBoudry\PhpReference\Definition\HasTagApi;
use JulienBoudry\PhpReference\Definition\IsPubliclyAccessible;
use JulienBoudry\PhpReference\Definition\HasTagBeta;

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

### Use only the configuration file
```bash
php bin/php-reference generate:documentation
```

### Override the namespace from the command line
```bash
php bin/php-reference generate:documentation MyNamespace
```

### Use a specific API definition
```bash
php bin/php-reference generate:documentation --api=public
php bin/php-reference generate:documentation --api=beta
```

### Use an alternative configuration file
```bash
php bin/php-reference generate:documentation --config=/path/to/config.php
```

### Override multiple options
```bash
php bin/php-reference generate:documentation MonNamespace --output=/tmp/docs --append --api=public
```

## Example Configuration Files

### Basic configuration
```php
<?php
return [
    'namespace' => 'App\\',
    'output' => getcwd() . '/api-docs',
    'append' => false,
    'api' => 'HasTagApi', // Uses HasTagApi
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
];
```

### Use a custom configuration file
```bash
php bin/php-reference generate:documentation --config=/path/to/my-config.php
```

## Available Options

| Option | Shortcut | Description | Config example | CLI example |
|--------|----------|-------------|----------------|-------------|
| namespace | - | Namespace to analyze | `'namespace' => 'Mon\\Namespace'` | `MonNamespace` |
| output | `-o` | Output directory | `'output' => '/path/to/docs'` | `--output=/path/to/docs` |
| append | `-a` | Do not clean before generation | `'append' => true` | `--append` |
| all-public | `-p` | Include all public code | `'all-public' => true` | `--all-public` |
| config | `-c` | Configuration file path | - | `--config=/custom/path.php` |
