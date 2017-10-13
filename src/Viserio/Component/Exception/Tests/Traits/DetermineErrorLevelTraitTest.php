<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Exception\Traits\DetermineErrorLevelTrait;

class DetermineErrorLevelTraitTest extends TestCase
{
    use DetermineErrorLevelTrait;

    public function testIsLevelFatal() {
        self::assertFalse(self::isLevelFatal(\E_DEPRECATED));
        self::assertTrue(self::isLevelFatal(\E_ERROR));
        self::assertTrue(self::isLevelFatal(\E_PARSE));
        self::assertTrue(self::isLevelFatal(\E_CORE_ERROR));
        self::assertTrue(self::isLevelFatal(\E_CORE_WARNING));
        self::assertTrue(self::isLevelFatal(\E_COMPILE_ERROR));
        self::assertTrue(self::isLevelFatal(\E_COMPILE_WARNING));
    }
}
