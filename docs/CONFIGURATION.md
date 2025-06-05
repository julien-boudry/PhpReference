# Configuration

PhpReference supporte un fichier de configuration PHP pour éviter de saisir les arguments en ligne de commande à chaque fois.

## Fichier de configuration

Créez un fichier `reference.php` à la racine de votre projet :

```php
<?php

use JulienBoudry\PhpReference\Definition\HasTagApi;
use JulienBoudry\PhpReference\Definition\IsPubliclyAccessible;

return [
    // Le namespace pour lequel générer la documentation
    'namespace' => 'MonNamespace\\MonProjet',

    // Répertoire de sortie pour la documentation générée
    'output' => __DIR__ . '/docs',

    // Ne pas nettoyer le répertoire de sortie avant la génération
    'append' => false,

    // Définition de l'API publique - peut être :
    // - Une instance d'une classe implémentant PublicApiDefinitionInterface
    // - Une chaîne correspondant à une définition enregistrée ('api', 'public')
    'api' => new HasTagApi(), // ou 'api' en string
];
```

## Définitions d'API disponibles

### Via chaîne de caractères (CLI et config)

- **`api`** : Inclut les éléments marqués avec `@api` (par défaut)
- **`public`** : Inclut tous les éléments publics
- **`beta`** : Inclut les éléments marqués avec `@beta`

### Via objet (config uniquement)

```php
use JulienBoudry\PhpReference\Definition\HasTagApi;
use JulienBoudry\PhpReference\Definition\IsPubliclyAccessible;
use JulienBoudry\PhpReference\Definition\HasTagBeta;

// Inclut uniquement les éléments avec @api
'api' => new HasTagApi(),

// Inclut tous les éléments publics
'api' => new IsPubliclyAccessible(),
```

## Créer une définition personnalisée

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

        // Votre logique personnalisée ici
        return $reflectionWrapper->hasTagCustom; // Par exemple
    }
}

// Dans votre config
return [
    'api' => new MyCustomDefinition(),
    // ...
];
```

## Priorité des arguments

La priorité est la suivante (du plus important au moins important) :

1. **Arguments en ligne de commande** (priorité absolue)
2. **Fichier de configuration**
3. **Valeurs par défaut**

## Exemples d'utilisation

### Utiliser uniquement le fichier de configuration
```bash
php bin/php-reference generate:documentation
```

### Surcharger le namespace depuis la ligne de commande
```bash
php bin/php-reference generate:documentation MonAutreNamespace
```

### Utiliser une définition d'API spécifique
```bash
php bin/php-reference generate:documentation --api=public
php bin/php-reference generate:documentation --api=beta
```

### Utiliser un fichier de configuration alternatif
```bash
php bin/php-reference generate:documentation --config=/path/to/config.php
```

### Surcharger plusieurs options
```bash
php bin/php-reference generate:documentation MonNamespace --output=/tmp/docs --append --api=public
```

## Fichiers de configuration d'exemple

### Configuration basique
```php
<?php
return [
    'namespace' => 'App\\',
    'output' => getcwd() . '/api-docs',
    'append' => false,
    'api' => 'HasTagApi', // Utilise HasTagApi
];
```

### Configuration avancée
```php
<?php

use JulienBoudry\PhpReference\Definition\IsPubliclyAccessible;

return [
    'namespace' => 'MyLibrary\\',
    'output' => __DIR__ . '/public-api-docs',
    'append' => true,
    'api' => new IsPubliclyAccessible(), // Instance directe
];
```

### Utiliser un fichier de configuration personnalisé
```bash
php bin/php-reference generate:documentation --config=/path/to/my-config.php
```

## Options disponibles

| Option | Raccourci | Description | Exemple config | Exemple CLI |
|--------|-----------|-------------|----------------|-------------|
| namespace | - | Namespace à analyser | `'namespace' => 'Mon\\Namespace'` | `MonNamespace` |
| output | `-o` | Répertoire de sortie | `'output' => '/path/to/docs'` | `--output=/path/to/docs` |
| append | `-a` | Ne pas nettoyer avant génération | `'append' => true` | `--append` |
| all-public | `-p` | Inclure tout le code public | `'all-public' => true` | `--all-public` |
| config | `-c` | Chemin du fichier de config | - | `--config=/custom/path.php` |
