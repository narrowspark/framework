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

namespace Viserio\Component\Container\Tests\UnitTest\Definition;

use Viserio\Component\Container\Definition\ArrayDefinition;
use Viserio\Component\Container\Definition\ParameterDefinition;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Log\Exception\Exception;

/**
 * @internal
 *
 * @small
 */
final class ArrayDefinitionTest extends AbstractDefinitionTest
{
    /**
     * {@inheritdoc}
     */
    protected function getValue(): array
    {
        return [
            1 => 'int_key',
            'string' => 'string',
            'int' => 0,
            'float' => 1.1,
            'bool' => false,
            'array' => [
                1 => 'int_key',
                'string' => 'string',
                'int' => 0,
                'float' => 1.1,
                'bool' => false,
                'object' => new \stdClass(),
                'null' => null,
                'anoObject' => new class() {
                    /** @var string */
                    public $test = 'test';
                },
                Exception::class => Exception::class,
            ],
            'object' => new \stdClass(),
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

    /**
     * {@inheritdoc}
     */
    protected function getDefinition(): ArrayDefinition
    {
        $value = $this->value;
        $value['string'] = new ParameterDefinition('string', 'string');

        return new ArrayDefinition($this->getDefinitionName(), $value, DefinitionContract::SINGLETON);
    }
}
