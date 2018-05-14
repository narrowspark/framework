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

namespace Viserio\Component\OptionsResolver\Tests\Container\Definition;

use Viserio\Component\OptionsResolver\Container\Definition\OptionDefinition;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionDefaultOptionsConfiguration;

/**
 * @internal
 *
 * @small
 */
final class OptionDefinitionTest extends AbstractOptionDefinitionTest
{
    /**
     * @return object
     */
    protected function getObject(): object
    {
        return new OptionDefinition('params', $this->getOptionClassName());
    }

    /**
     * @return string
     */
    protected function getOptionClassName(): string
    {
        return ConnectionDefaultOptionsConfiguration::class;
    }

    public function testSetConfigAndGetValue(): void
    {
        $object = $this->getObject();
        $object::$configClass = $object->getClass();

        $object->setConfig($expected = [
            'doctrine' => [
                'connection' => [
                    'foo' => 'test',
                ],
            ],
        ]);
        $optionClassName = $this->getOptionClassName();

        self::assertSame(\array_merge($optionClassName::getDefaultOptions(), $expected), $object->getValue());
    }

    public function getKey(): void
    {
        $object = $this->getObject();

        self::assertSame('params', $object->getKey());
    }
}
