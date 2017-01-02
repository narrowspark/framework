<?php
declare(strict_types=1);
namespace Viserio\Support\Tests\Traits;

use Viserio\Support\Tests\Fixture\MacroTest;
use Viserio\Support\Traits\MacroableTrait;
use PHPUnit\Framework\TestCase;

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
