<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\UnitTest\Definition;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Definition\AliasDefinition;
use Viserio\Component\Contract\Container\Exception\InvalidArgumentException;

/**
 * @internal
 */
final class AliasDefinitionTest extends TestCase
{
    /**
     * @var \Viserio\Component\Container\Definition\AliasDefinition
     */
    private $aliasDefinition;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->aliasDefinition = new AliasDefinition('alias', 'name');
    }

    public function testGetName(): void
    {
        static::assertSame('name', $this->aliasDefinition->getName());
    }

    public function testGetAlias(): void
    {
        static::assertSame('alias', $this->aliasDefinition->getAlias());
    }

    public function testDeprecated(): void
    {
        $this->aliasDefinition->setDeprecated();

        static::assertSame('The [alias] binding alias is deprecated. You should stop using it, as it will soon be removed.', $this->aliasDefinition->getDeprecationMessage());
        static::assertTrue($this->aliasDefinition->isDeprecated());

        $this->aliasDefinition->setDeprecated(true, '[%s]');

        static::assertSame('[alias]', $this->aliasDefinition->getDeprecationMessage());
    }

    public function testSetDeprecatedThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The deprecation template must contain the [%s] placeholder.');

        $this->aliasDefinition->setDeprecated(false, 'empty');
    }
}
