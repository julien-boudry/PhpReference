# Copilot Instructions for PhpReference

## Project Overview
PhpReference is a PHP documentation generator that analyzes PHP namespaces using reflection and generates Markdown documentation. It's a console application built with Symfony Console that converts PHP classes, methods, and properties into structured documentation with cross-references and links.

## Architecture & Key Components

### Core Flow: Namespace → Reflection → Templates → Markdown
1. **CodeIndex** (`src/CodeIndex.php`) - Discovers and wraps all classes in a namespace using `HaydenPierce\ClassFinder`
2. **Execution** (`src/Execution.php`) - Singleton orchestrator that manages the documentation generation process
3. **Writer Classes** (`src/Writer/`) - Generate specific documentation pages using Latte templates
4. **Wrapper Classes** (`src/Reflect/`) - Provide enhanced reflection capabilities with documentation parsing

### Critical Singleton Pattern
`Execution::$instance` is accessed globally throughout the codebase. Always ensure it's properly initialized before using reflection wrappers.

### Wrapper Hierarchy (src/Reflect/)
- `ReflectionWrapper` (abstract base) - Common reflection + PHPDoc parsing
- `ClassWrapper` - Wraps ReflectionClass/ReflectionEnum, determines API visibility
- `ClassElementWrapper` - Base for class members (methods, properties, constants)
- `MethodWrapper`, `PropertyWrapper`, `ClassConstantWrapper` - Specific element types

Key pattern: Wrappers determine if elements are part of the public API via `willBeInPublicApi` property.

## Development Workflows

### Running the Generator
```bash
# Generate docs for configured namespace (see reference.php)
php bin/php-reference

# Generate docs for specific namespace
php bin/php-reference MyNamespace\\ToDocument

# Custom output directory
php bin/php-reference --output=/path/to/docs
```

### Testing & Quality
```bash
composer test          # Run Pest tests
composer phpstan        # Static analysis
composer lint           # PHP-CS-Fixer formatting
```

## Project-Specific Patterns

### Configuration System
- `reference.php` in project root contains default settings
- CLI arguments override config file values
- `Config` class provides unified access with fallbacks

### Public API Detection
Two main strategies via `PublicApiDefinitionInterface`:
- `HasTagApi` - Requires explicit `@api` PHPDoc tags
- `IsPubliclyAccessible` - Includes all public elements

### Template System (Latte)
- Templates in `src/Template/` use Latte syntax with imports
- ContentType set to Text to disable HTML escaping for Markdown output
- Writers extend `AbstractWriter` and implement `makeContent()`

### Type Link Generation
`Util::getTypeMd()` converts `ReflectionType` to Markdown with automatic cross-linking:
- Uses recursive processing for Union/Intersection types
- Links to generated documentation for project classes
- Handles nullable types via `ReflectionNamedType::allowsNull()`

### URL Linking Pattern
`UrlLinker` generates relative paths between documentation pages. Writers use this to create cross-references between classes, methods, and properties.

## Integration Points

### External Dependencies
- **Latte** - Template engine for Markdown generation
- **League/Flysystem** - File system abstraction for output
- **PHPDocumentor/ReflectionDocBlock** - PHPDoc parsing
- **Symfony/Console** - CLI interface with prompts

### Output Structure
```
output/
├── README.md (API summary)
└── ref/
    └── Namespace/
        ├── ClassName.md
        ├── ClassName/
        │   ├── methodName.md
        │   └── propertyName.md
```

## Common Gotchas
- Always call `Execution::$instance` after initialization in command
- Wrapper classes use `WeakReference` for parent relationships to avoid circular references
- Template paths are resolved relative to `src/Template/` directory
- Type linking only works for classes within the indexed namespace
