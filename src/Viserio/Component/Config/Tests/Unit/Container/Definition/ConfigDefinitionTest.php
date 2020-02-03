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

namespace Viserio\Component\Config\Tests\Unit\Unit\Container\Definition;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\Container\Definition\ConfigDefinition;
use Viserio\Component\Config\Tests\Fixture\ConnectionComponentDefaultConfigConfiguration;

/**
 * @internal
 *
 * @small
 */
final class ConfigDefinitionTest extends TestCase
{
    private ConfigDefinition $definition;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->definition = new ConfigDefinition(ConnectionComponentDefaultConfigConfiguration::class, 'test');
    }

    public function testGetClass(): void
    {
        self::assertSame(ConnectionComponentDefaultConfigConfiguration::class, $this->definition->getClass());
    }

    public function testGetId(): void
    {
        self::assertSame('test', $this->definition->getId());
    }
}
