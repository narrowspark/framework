<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Support\Tests\Fixture\MacroTest;
use Viserio\Component\Support\Traits\MacroableTrait;

class MacroableTraitTest extends TestCase
{
    use MacroableTrait;

    public function testRegisterMacro()
    {
        $macroable = new self();

        $macroable::macro(__CLASS__, function () {
            return 'Macro';
        });

        self::assertEquals('Macro', $macroable::{__CLASS__}());
    }

    public function testRegisterMacroAndCallWithoutStatic()
    {
        $macroable = new self();

        $macroable::macro(__CLASS__, function () {
            return 'Macro';
        });

        self::assertEquals('Macro', $macroable->{__CLASS__}());
    }

    public function testWhenCallingMacroClosureIsBoundToObject()
    {
        MacroTest::macro('tryInstance', function () {
            return $this->protectedVariable;
        });

        MacroTest::macro('tryStatic', function () {
            return static::getProtectedStatic();
        });

        $instance = new MacroTest();
        $result   = $instance->tryInstance();

        self::assertEquals('instance', $result);

        $result = MacroTest::tryStatic();

        self::assertEquals('static', $result);
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Method dontExist does not exist.
     */
    public function testBadFunctionCall()
    {
        $instance = new MacroTest();
        $instance->dontExist();
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Method dontExist does not exist.
     */
    public function testBadStaticFunctionCall()
    {
        MacroTest::dontExist();
    }
}
