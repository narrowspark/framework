<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\UnitTest\Definition;

use Viserio\Component\Container\Definition\ClosureDefinition;
use Viserio\Component\Contract\Container\Compiler\Definition as DefinitionContract;
use Viserio\Component\Contract\Container\Types as TypesContract;

/**
 * @internal
 */
final class ClosureDefinitionTest extends AbstractDefinitionTest
{
    public function testGetValue(): void
    {
        static::assertNull($this->definition->getValue());
    }

    public function testCompileWithSingletonClosure(): void
    {
        static::assertSame('        return $this->services[\'test\'] = (static function () {
    return \'test\';
})();', $this->definition->compile());
    }

    public function testCompileWithServiceClosure(): void
    {
        $definition = new ClosureDefinition($this->getDefinitionName(), $this->value, TypesContract::SERVICE);

        static::assertSame('        return (static function () {
    return \'test\';
})();', $definition->compile());
    }

    protected function getValue()
    {
        return function () {
            return 'test';
        };
    }

    protected function getDefinitionName(): string
    {
        return 'test';
    }

    protected function getDefinition(): DefinitionContract
    {
        return new ClosureDefinition($this->getDefinitionName(), $this->value, TypesContract::SINGLETON);
    }
}
