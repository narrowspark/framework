<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\UnitTest\Definition;

use Viserio\Component\Container\Definition\FactoryDefinition;
use Viserio\Component\Contract\Container\Compiler\Definition as DefinitionContract;
use Viserio\Component\Contract\Container\Types as TypesContract;

/**
 * @internal
 */
final class FactoryDefinitionTest extends AbstractDefinitionTest
{
    public function testGetValue(): void
    {
        static::assertNull($this->definition->getValue());
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
        return new FactoryDefinition($this->getDefinitionName(), $this->value, TypesContract::SINGLETON);
    }
}
