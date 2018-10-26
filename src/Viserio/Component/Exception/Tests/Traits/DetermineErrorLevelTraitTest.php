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
        $this->assertFalse(self::isLevelFatal(\E_DEPRECATED));
        $this->assertTrue(self::isLevelFatal(\E_ERROR));
        $this->assertTrue(self::isLevelFatal(\E_PARSE));
        $this->assertTrue(self::isLevelFatal(\E_CORE_ERROR));
        $this->assertTrue(self::isLevelFatal(\E_CORE_WARNING));
        $this->assertTrue(self::isLevelFatal(\E_COMPILE_ERROR));
        $this->assertTrue(self::isLevelFatal(\E_COMPILE_WARNING));
    }
}
