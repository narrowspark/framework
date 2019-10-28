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

use Viserio\Component\OptionsResolver\Container\Definition\DimensionsOptionDefinition;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentConfiguration;
use Viserio\Contract\OptionsResolver\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 */
final class DimensionsOptionDefinitionTest extends AbstractOptionDefinitionTest
{
    public function testThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided class [stdClass] didn\'t implement the [Viserio\Contract\OptionsResolver\RequiresComponentConfig] interface or one of the parent interfaces.');

        new DimensionsOptionDefinition(\stdClass::class);
    }

    public function testGetClassDimensions(): void
    {
        $object = $this->getObject();
        $object::$classDimensions = $object->getClassDimensions();

        $classOption = $this->getOptionClassName();

        self::assertSame($classOption::getDimensions(), $object::getDimensions());
    }

    /**
     * @return object
     */
    protected function getObject(): object
    {
        return new DimensionsOptionDefinition($this->getOptionClassName());
    }

    /**
     * @return string
     */
    protected function getOptionClassName(): string
    {
        return ConnectionComponentConfiguration::class;
    }
}
