<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
 * @small
 */
final class IteratorDefinitionTest extends AbstractDefinitionTest
{
    /** @var \Viserio\Component\Container\Definition\IteratorDefinition */
    protected $definition;

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

    public function testSetAddAndGetParameters(): void
    {
        self::assertSame($this->definition, $this->definition->setArgument($this->value), '->setArgument() implements a fluent interface');

        self::assertTrue($this->definition->getChange('arguments'));
        self::assertCount($this->value, $this->definition->getArgument());
    }
}
