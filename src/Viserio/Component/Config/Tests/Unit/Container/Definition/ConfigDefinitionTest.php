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

namespace Viserio\Component\Config\Tests\Unit\Unit\Container\Definition;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\Container\Definition\ConfigDefinition;
use Viserio\Component\Config\Tests\Fixture\ConnectionComponentDefaultConfigConfiguration;

/**
 * @internal
 *
 * @small
 * @coversNothing
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
