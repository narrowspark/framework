<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\UnitTest\Compiler;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Compiler\CompileHelper;

/**
 * @internal
 */
final class CompileHelperTest extends TestCase
{
    public function testGetNextAvailableVariableName(): void
    {
        $this->assertEquals('$a', CompileHelper::getNextAvailableVariableName('a', []));
        $this->assertEquals('$a', CompileHelper::getNextAvailableVariableName('$a', []));
        $this->assertEquals('$a1', CompileHelper::getNextAvailableVariableName('$a', ['$a']));
        $this->assertEquals('$a2', CompileHelper::getNextAvailableVariableName('$a', ['$a', '$a1']));
        $this->assertEquals('$a10', CompileHelper::getNextAvailableVariableName('$a', ['$a', '$a1', '$a2', '$a3', '$a4', '$a5', '$a6', '$a7', '$a8', '$a9']));
        $this->assertEquals('$a10', CompileHelper::getNextAvailableVariableName('10', []));
        $this->assertEquals('$b', CompileHelper::getNextAvailableVariableName('#${}b', []));
    }
}
