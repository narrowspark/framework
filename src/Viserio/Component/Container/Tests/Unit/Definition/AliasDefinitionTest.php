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

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Definition\AliasDefinition;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Definition\AliasDefinition
 *
 * @small
 */
final class AliasDefinitionTest extends TestCase
{
    /** @var \Viserio\Component\Container\Definition\AliasDefinition */
    private $aliasDefinition;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->aliasDefinition = new AliasDefinition('name', 'alias');
    }

    public function testGetName(): void
    {
        self::assertSame('name', $this->aliasDefinition->getName());

        $this->aliasDefinition->setName($name = 'foo');

        self::assertSame($name, $this->aliasDefinition->getName());
    }

    public function testGetAlias(): void
    {
        self::assertSame('alias', $this->aliasDefinition->getAlias());
    }

    public function testDeprecated(): void
    {
        $this->aliasDefinition->setDeprecated();

        self::assertSame('The [alias] service alias is deprecated. You should stop using it, as it will be removed in the future.', $this->aliasDefinition->getDeprecationMessage());
        self::assertTrue($this->aliasDefinition->isDeprecated());

        $this->aliasDefinition->setDeprecated(true, '[%s]');

        self::assertSame('[alias]', $this->aliasDefinition->getDeprecationMessage());
    }

    public function testSetDeprecatedThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The deprecation template must contain the [%s] placeholder.');

        $this->aliasDefinition->setDeprecated(false, 'empty');
    }
}
