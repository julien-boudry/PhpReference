# Copilot Instructions for PhpReference

## Project Overview
PhpReference is a PHP documentation generator that analyzes PHP namespaces using reflection and generates comprehensive Markdown documentation. It's a console application built with Symfony Console that converts PHP classes, enums, methods, properties, and constants into structured documentation with cross-references and internal links.

## Architecture & Key Components

### Core Flow: Namespace → Reflection → Templates → Markdown
1. **CodeIndex** (`src/CodeIndex.php`) - Discovers all classes in a namespace using `HaydenPierce\ClassFinder`, wraps them, and organizes by namespace
2. **Execution** (`src/Execution.php`) - Singleton orchestrator that manages the entire documentation generation process
3. **Writer Classes** (`src/Writer/`) - Generate specific documentation pages using Latte templates
4. **Wrapper Classes** (`src/Reflect/`) - Provide enhanced reflection capabilities with PHPDoc parsing via `phpDocumentor\Reflection\DocBlock`
5. **Input Classes** (`src/Template/Input/`) - Data transfer objects that prepare data for Latte templates

### Critical Singleton Pattern
`Execution::$instance` is accessed globally throughout the codebase. Always ensure it's properly initialized before using reflection wrappers.

### Wrapper Hierarchy (src/Reflect/)
- `ReflectionWrapper` (abstract base) - Common reflection functionality + PHPDoc parsing
- `ClassWrapper` - Wraps ReflectionClass with property hooks for methods/properties/constants
- `EnumWrapper` - Extends ClassWrapper for enum-specific features
- `ClassElementWrapper` - Abstract base for class members (methods, properties, constants)
- `MethodWrapper`, `PropertyWrapper`, `ClassConstantWrapper` - Specific element types
- `FunctionWrapper` - For standalone functions
- `ParameterWrapper` - For function/method parameters
- `NamespaceWrapper` - Groups classes by namespace with hierarchy support

### Wrapper Capabilities (src/Reflect/Capabilities/)
Interfaces that define special capabilities:
- `HasParentInterface` - For elements with parent relationships (ClassElementWrapper, ParameterWrapper)
- `SignatureInterface` - For elements with signatures (methods, functions, parameters)
- `WritableInterface` - For elements that can be written to documentation pages

### Wrapper Structure Traits (src/Reflect/Structure/)
- `CanThrow` - For elements that can throw exceptions (@throws parsing)
- `HasType` - For elements with type information (properties, parameters, return types)
- `IsFunction` - For function-like elements (methods, functions)

Key pattern: Wrappers determine if elements are part of the public API via `willBeInPublicApi` property (computed dynamically from `PublicApiDefinitionInterface`).

## Development Workflows

### Running the Generator
```bash
# Generate docs using configuration from reference.php
php bin/php-reference

# Or use the full command name
php bin/php-reference generate:documentation

# Generate docs for specific namespace (overrides config)
php bin/php-reference MyNamespace\\ToDocument

# Custom output directory
php bin/php-reference --output=/path/to/docs
php bin/php-reference -o /path/to/docs

# Use a different API definition (overrides config)
php bin/php-reference --api=IsPubliclyAccessible
php bin/php-reference --api=HasTagApi

# Append to existing docs without cleaning output directory first
php bin/php-reference --append
php bin/php-reference -a

# Specify custom configuration file
php bin/php-reference --config=/path/to/custom-reference.php
php bin/php-reference -c /path/to/custom-reference.php

# Custom index file name (default: 'readme')
php bin/php-reference --index-file-name=index

# Combine options
php bin/php-reference MyNamespace\\MyProject -o ./docs -a --api=IsPubliclyAccessible
```

### Testing & Quality
```bash
composer test          # Run Pest tests (PHPUnit/Pest)
composer phpstan       # Static analysis with PHPStan level max
composer lint          # PHP-CS-Fixer check and fix
```

## Project-Specific Patterns

### Configuration System
- `reference.php` in project root contains default settings (namespace, output dir, API definition, no-interaction, etc.)
- CLI arguments override config file values (priority: CLI > Config File > Defaults)
- `Config` class (`src/Config.php`) provides unified access with `get()`, `set()`, `has()`, `all()` methods
- Config file is loaded in `GenerateDocumentationCommand::initialize()`, then merged with CLI args via `mergeWithCliArgs()`
- Supports both string-based API definitions ('IsPubliclyAccessible', 'HasTagApi') and object instances
- The `getApiDefinition()` method resolves strings to actual definition objects (case-insensitive matching)

### Public API Detection
Two main strategies via `PublicApiDefinitionInterface`:
- `HasTagApi` - Requires explicit `@api` PHPDoc tags (default, strictest)
- `IsPubliclyAccessible` - Includes all public elements (most permissive)
- Both extend `Base` class which provides `baseExclusion()` to filter out `@internal` tagged and non-user-defined elements
- Custom definitions can be created by extending `Base` and implementing `PublicApiDefinitionInterface`
- The API definition is accessed via `Execution::$instance->publicApiDefinition`

### Template System (Latte)
- Main templates in `src/Template/`
- Reusable parts in `src/Template/parts/`
- ContentType set to Text to disable HTML escaping (output is Markdown, not HTML)
- Input classes in `src/Template/Input/` prepare data for templates (e.g., `ClassPageInput`, `MethodPageInput`)
- Writers extend `AbstractWriter` and implement `makeContent()` which uses Latte to render templates
- Template directory constant: `AbstractWriter::TEMPLATE_DIR` points to `src/Template/`

### Type Link Generation
`Util::getTypeMd()` converts `ReflectionType` to Markdown with automatic cross-linking:
- Uses recursive `processReflectionType()` for Union types (`|`) and Intersection types (`&`)
- Links to generated documentation for project classes via `UrlLinker`
- Handles nullable types via `ReflectionNamedType::allowsNull()`
- External types (PHP built-ins like string, int, array) are rendered without links
- Uses `Execution::$instance->codeIndex` to check if a class is part of the documented namespace

### URL Linking Pattern
`UrlLinker` generates relative paths between documentation pages:
- Creates links between classes, methods, properties, constants, and namespaces
- Used by Writers to generate cross-references in documentation
- Handles different page types (class pages, element pages, namespace pages)
- Maintains correct relative paths based on directory structure

### Error Handling
- `ErrorCollector` (`src/Log/ErrorCollector.php`) accumulates errors during generation without stopping the process
- `CollectedError` represents individual errors with severity levels via `ErrorLevel` enum
- Errors displayed at the end of generation with summary counts
- Use `-v` verbose flag to see full error details
- Progress tracking uses Laravel Prompts: `progress()`, `info()`, `warning()`, `error()`, `note()`, `confirm()`

## Integration Points

### Output Structure
```
output/
├── readme.md (API summary - configurable via --index-file-name)
└── ref/
    └── Namespace/
        ├── ClassName/
        │   ├── class_ClassName.md
        │   ├── methodName.md
        │   └── propertyName.md
        └── SubNamespace/
            └── ...
```

### Modern PHP Features Used
- Property hooks (PHP 8.4+): `public array $methods { get => ... }`
- Asymmetric visibility (PHP 8.4+): `public protected(set) array $namespaces`
- Typed properties and readonly properties
- Match expressions for type-safe conditionals
- Constructor property promotion
- Named arguments in function calls

## Common Gotchas
- Always call `Execution::$instance` after initialization in command
- Wrapper classes use `WeakReference` for parent relationships to avoid circular references (see `ClassElementWrapper::$classReference` and `ParameterWrapper::$parentFunctionReference`)
- Template paths are resolved relative to `src/Template/` directory
- Type linking only works for classes within the indexed namespace
- Modern PHP features (property hooks, asymmetric visibility) require PHP 8.4+
- Latte templates use Text ContentType - no HTML escaping, output is Markdown
- Config file supports both string values ('api', 'public') and object instances for API definitions
- The `--append` flag prevents cleaning the output directory - useful for incremental documentation
