<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Tests\Fixtures;

/**
 * Fixture for testing PHP 8.4+ features.
 *
 * Contains property hooks and asymmetric visibility.
 *
 * @api
 */
class Php84FeaturesFixture
{
    // ========== Asymmetric Visibility ==========

    /**
     * A public property with protected set visibility.
     */
    public protected(set) string $publicProtectedSet = 'default';

    /**
     * A public property with private set visibility.
     */
    public private(set) int $publicPrivateSet = 0;

    // ========== Property Hooks ==========

    /**
     * Backing field for the hooked property.
     */
    private string $nameValue = '';

    /**
     * A property with get and set hooks.
     */
    public string $hookedName {
        get => strtoupper($this->nameValue);
        set => $this->nameValue = trim($value);
    }

    /**
     * A virtual property (no backing storage).
     */
    public int $doubleValue {
        get => $this->publicPrivateSet * 2;
    }

    /**
     * A computed readonly-like property.
     */
    public string $computedLabel {
        get => "Label: {$this->hookedName}";
    }

    /**
     * Array for hooked array property.
     */
    private array $items = [];

    /**
     * A property with array type and hooks.
     *
     * @var string[]
     */
    public array $hookedItems {
        get => $this->items;
        set {
            $this->items = array_map('strval', $value);
        }
    }

    // ========== Constructor with new features ==========

    /**
     * Constructor demonstrating asymmetric visibility in promotion.
     *
     * @param string $constructorArg A constructor argument with public/private(set).
     */
    public function __construct(
        public private(set) string $constructorArg = 'default_arg'
    ) {}

    // ========== Methods ==========

    /**
     * Set the name value (for testing hooks).
     *
     * @param string $name The name to set.
     */
    public function setName(string $name): void
    {
        $this->hookedName = $name;
    }

    /**
     * Get the raw name value (bypassing hook).
     *
     * @return string The raw name value.
     */
    public function getRawName(): string
    {
        return $this->nameValue;
    }

    /**
     * Increment the private set counter (internal use).
     */
    protected function incrementCounter(): void
    {
        $this->publicPrivateSet++;
    }
}
