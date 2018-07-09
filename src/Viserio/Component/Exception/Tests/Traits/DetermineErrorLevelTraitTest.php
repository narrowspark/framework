<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Exception\Traits\DetermineErrorLevelTrait;

/**
 * @internal
 */
final class DetermineErrorLevelTraitTest extends TestCase
{
    use DetermineErrorLevelTrait;

    public function testIsLevelFatal(): void
    {
        static::assertFalse(self::isLevelFatal(\E_DEPRECATED));
        static::assertTrue(self::isLevelFatal(\E_ERROR));
        static::assertTrue(self::isLevelFatal(\E_PARSE));
        static::assertTrue(self::isLevelFatal(\E_CORE_ERROR));
        static::assertTrue(self::isLevelFatal(\E_CORE_WARNING));
        static::assertTrue(self::isLevelFatal(\E_COMPILE_ERROR));
        static::assertTrue(self::isLevelFatal(\E_COMPILE_WARNING));
    }
}
