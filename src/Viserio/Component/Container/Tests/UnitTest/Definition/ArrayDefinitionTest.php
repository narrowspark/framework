<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\UnitTest\Definition;

use Symfony\Component\VarExporter\VarExporter;
use Viserio\Component\Container\Definition\ArrayDefinition;
use Viserio\Component\Container\Definition\ParameterDefinition;
use Viserio\Component\Contract\Container\Compiler\Definition as DefinitionContract;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Container\Types as TypesContract;
use Viserio\Component\Contract\Log\Exception\Exception;

/**
 * @internal
 */
final class ArrayDefinitionTest extends AbstractDefinitionTest
{
    public function testGetDebugInfo(): void
    {
        $info = $this->definition->getDebugInfo();

        static::assertSame(VarExporter::export($this->getValue()), $info);
    }

    public function testResolve(): void
    {
        $this->definition->resolve($this->mock(ContainerContract::class));

        static::assertSame($this->value, $this->definition->getValue());
        static::assertTrue($this->definition->isResolved());
    }

    public function testResolveWithExtend(): void
    {
        $this->definition->addExtender(function ($container, $value) {
            $value[1] = 'test';

            return $value;
        });

        $this->definition->resolve($this->mock(ContainerContract::class));

        $expected    = $this->value;
        $expected[1] = 'test';

        static::assertSame($expected, $this->definition->getValue());
        static::assertTrue($this->definition->isResolved());
    }

    public function testCompile(): void
    {
        $compile = $this->definition->compile();

        static::assertSame('        return $this->services[\'test\'] = ' . VarExporter::export($this->getValue()) . ';', $compile);
    }

    public function testCompileWithExtend(): void
    {
        $this->definition->addExtender(function ($container, $value) {
            $value[1] = 'test';

            return $value;
        });

        $this->definition->setExtendMethodName('extend');

        $compile = $this->definition->compile();

        static::assertEquals(
            '        $binding   = ' . VarExporter::export($this->getValue()) . ';' . \PHP_EOL . '        $extenders = [' . \PHP_EOL . '        static function ($container, $value) {' . "\n" .
            '    $value[1] = \'test\';' . "\n" . '    return $value;' . "\n" . '}' . \PHP_EOL . '        ];' . \PHP_EOL . \PHP_EOL .
            '        $this->extend($extenders, $binding);' . \PHP_EOL . \PHP_EOL .
            '        return $this->services[\'test\'] = $binding;',
            $compile
        );
    }

    protected function getValue(): array
    {
        return [
            1        => 'int_key',
            'string' => 'string',
            'int'    => 0,
            'float'  => 1.1,
            'bool'   => false,
            'array'  => [
                1        => 'int_key',
                'string' => 'string',
                'int'    => 0,
                'float'  => 1.1,
                'bool'   => false,
                'object' => new \stdClass(),
                'null'   => null,
                //                'anoObject' => new class() {
                //                    public $test = 'test';
                //                },
                Exception::class => Exception::class,
            ],
            'object' => new \stdClass(),
            'null'   => null,
            //            'anoObject' => new class() {
            //                public $test = 'test';
            //            },
            Exception::class => Exception::class,
        ];
    }

    protected function getDefinitionName(): string
    {
        return 'test';
    }

    protected function getDefinition(): DefinitionContract
    {
        $value           = $this->value;
        $value['string'] = new ParameterDefinition('string', 'string', TypesContract::SINGLETON);

        return new ArrayDefinition($this->getDefinitionName(), $value, TypesContract::SINGLETON);
    }
}
