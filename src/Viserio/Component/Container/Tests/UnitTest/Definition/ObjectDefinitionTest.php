<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\UnitTest\Definition;

use Viserio\Component\Container\Definition\ObjectDefinition;
use Viserio\Component\Contract\Container\Compiler\Definition as DefinitionContract;
use Viserio\Component\Contract\Container\Types as TypesContract;

/**
 * @internal
 */
final class ObjectDefinitionTest extends AbstractDefinitionTest
{
    public function testGetValue(): void
    {
        static::assertNull($this->definition->getValue());
    }

    protected function getValue()
    {
        return new \stdClass();
    }

    protected function getDefinitionName(): string
    {
        return 'test';
    }

    protected function getDefinition(): DefinitionContract
    {
        return new ObjectDefinition($this->getDefinitionName(), $this->value, TypesContract::SINGLETON);
    }
}
