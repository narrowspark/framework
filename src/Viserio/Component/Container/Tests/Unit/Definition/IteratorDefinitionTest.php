<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Container\Tests\Unit\Definition;

use stdClass;
use Viserio\Component\Container\Definition\IteratorDefinition;
use Viserio\Component\Container\Definition\ParameterDefinition;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Log\Exception\Exception;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Definition\IteratorDefinition
 *
 * @small
 */
final class IteratorDefinitionTest extends AbstractDefinitionTest
{
    /** @var \Viserio\Component\Container\Definition\IteratorDefinition */
    protected $definition;

    public function testSetAddAndGetArguments(): void
    {
        self::assertSame($this->definition, $this->definition->setArgument($this->value), '->setArgument() implements a fluent interface');

        self::assertTrue($this->definition->getChange('argument'));
        self::assertSame($this->value, $this->definition->getArgument());
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefinition(): IteratorDefinition
    {
        return new IteratorDefinition($this->getDefinitionName(), $this->value, DefinitionContract::SINGLETON);
    }

    /**
     * {@inheritdoc}
     */
    protected function getValue(): array
    {
        return [
            1 => 'int_key',
            'string' => new ParameterDefinition('string', 'string'),
            'int' => 0,
            'float' => 1.1,
            'bool' => false,
            'array' => [
                1 => 'int_key',
                'string' => 'string',
                'int' => 0,
                'float' => 1.1,
                'bool' => false,
                'object' => new stdClass(),
                'null' => null,
                'anoObject' => new class() {
                    /** @var string */
                    public $test = 'test';
                },
                Exception::class => Exception::class,
            ],
            'object' => new stdClass(),
            'null' => null,
            'anoObject' => new class() {
                /** @var string */
                public $test = 'test';
            },
            Exception::class => Exception::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefinitionName(): string
    {
        return 'test';
    }
}
