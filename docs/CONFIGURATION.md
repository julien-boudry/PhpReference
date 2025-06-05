# Configuration

PhpReference supporte un fichier de configuration PHP pour éviter de saisir les arguments en ligne de commande à chaque fois.

## Fichier de configuration

Créez un fichier `reference.php` à la racine de votre projet :

```php
<?php

return [
    // Le namespace pour lequel générer la documentation
    'namespace' => 'MonNamespace\\MonProjet',

    // Répertoire de sortie pour la documentation générée
    'output' => __DIR__ . '/docs',

    // Ne pas nettoyer le répertoire de sortie avant la génération
    'append' => false,

    // Inclure toutes les classes/méthodes/propriétés publiques,
    // même celles sans tags @api ou @internal
    'all-public' => true,
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

### Surcharger plusieurs options
```bash
php bin/php-reference generate:documentation MonNamespace --output=/tmp/docs --append
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
