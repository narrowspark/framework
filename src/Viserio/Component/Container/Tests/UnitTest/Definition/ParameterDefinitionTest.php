<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\UnitTest\Definition;

use Viserio\Component\Container\Definition\ParameterDefinition;
use Viserio\Component\Contract\Container\Compiler\Definition as DefinitionContract;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Container\Types as TypesContract;

/**
 * @internal
 */
final class ParameterDefinitionTest extends AbstractDefinitionTest
{
    public function testIsShared(): void
    {
        static::assertFalse($this->definition->isShared());
    }

    /**
     * @dataProvider parameterDefinitionProvider
     *
     * @param mixed $value
     *
     * @return void
     */
    public function testResolve($value): void
    {
        $definition = new ParameterDefinition('type', $value, TypesContract::SERVICE);

        $definition->resolve($this->mock(ContainerContract::class));

        static::assertSame($value, $definition->getValue());
    }

    public function parameterDefinitionProvider(): array
    {
        return [
            [1],
            [1.1],
            ['string'],
            [false],
            [null],
        ];
    }

    /**
     * @dataProvider parameterExtendDefinitionProvider
     *
     * @param mixed $value
     * @param mixed $extend
     * @param mixed $expected
     *
     * @return void
     */
    public function testResolveWithExtend($value, $extend, $expected): void
    {
        $definition = new ParameterDefinition('type', $value, TypesContract::SERVICE);

        $definition->addExtender(function ($container, $value) use ($extend) {
            return $value . $extend;
        });

        $definition->resolve($this->mock(ContainerContract::class));

        static::assertSame($expected, $definition->getValue());
    }

    public function parameterExtendDefinitionProvider(): array
    {
        return [
            [1, 1, '11'],
            [1.1, 2.1, '1.12.1'],
            ['string', 'test', 'stringtest'],
            [false, true, '1'],
            [null, 0, '0'],
        ];
    }

    public function testGetDebugInfo(): void
    {
        static::assertSame('Parameter (\'this is a string\')', $this->definition->getDebugInfo());
    }

    /**
     * @dataProvider parameterCompileDefinitionProvider
     *
     * @param mixed $expected
     * @param mixed $value
     * @param mixed $type
     *
     * @return void
     */
    public function testCompile($expected, $value, $type = TypesContract::SERVICE): void
    {
        $definition = new ParameterDefinition('compile', $value, $type);

        static::assertSame($expected, $definition->compile());
    }

    public function parameterCompileDefinitionProvider(): array
    {
        return [
            ['        return 1;', 1],
            ['        return $this->services[\'compile\'] = 1.1;', 1.1, TypesContract::SINGLETON],
            ['        return \'string\';', 'string'],
            ['        return $this->services[\'compile\'] = false;', false, TypesContract::PLAIN],
            ['        return null;', null],
        ];
    }

    public function testCompileWithExtend(): void
    {
        $this->definition->addExtender(function ($container, $value) {
            return $value;
        });

        $this->definition->setExtendMethodName('extend');

        static::assertSame('        $binding   = \'this is a string\';' . \PHP_EOL . '        $extenders = [' . \PHP_EOL . '        static function ($container, $value) {' . \PHP_EOL . '    return $value;' . \PHP_EOL . '}' . \PHP_EOL . '        ];' . \PHP_EOL . \PHP_EOL . '        $this->extend($extenders, $binding);' . \PHP_EOL . \PHP_EOL . '        return $binding;', $this->definition->compile());
    }

    protected function getValue(): string
    {
        return 'this is a string';
    }

    protected function getDefinitionName(): string
    {
        return 'test';
    }

    protected function getDefinition(): DefinitionContract
    {
        return new ParameterDefinition($this->getDefinitionName(), $this->value, TypesContract::SERVICE);
    }
}
