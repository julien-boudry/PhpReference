<?php

declare(strict_types=1);

namespace JulienBoudry\PhpReference\Tests\Fixtures;

/**
 * A fixture class for testing @see tag resolution.
 *
 * @api
 */
class SeeTagFixture
{
    /**
     * A static property.
     */
    public static string $staticProperty = 'static value';

    /**
     * A non-static property.
     */
    public string $regularProperty = 'regular value';

    /**
     * A static constant.
     */
    public const STATIC_CONSTANT = 'constant value';

    /**
     * A method that references a static property in its docblock.
     *
     * @see SeeTagFixture::$staticProperty
     */
    public function methodReferencingStaticProperty(): void {}

    /**
     * A method that references a regular property in its docblock.
     *
     * @see SeeTagFixture::$regularProperty
     */
    public function methodReferencingRegularProperty(): void {}

    /**
     * A method that references a constant in its docblock.
     *
     * @see SeeTagFixture::STATIC_CONSTANT
     */
    public function methodReferencingConstant(): void {}

    /**
     * A method that references another method.
     *
     * @see SeeTagFixture::methodReferencingStaticProperty()
     */
    public function methodReferencingMethod(): void {}

    /**
     * A method that references another method with short syntax.
     *
     * @see methodReferencingStaticProperty()
     */
    public function methodReferencingMethodShort(): void {}
}
