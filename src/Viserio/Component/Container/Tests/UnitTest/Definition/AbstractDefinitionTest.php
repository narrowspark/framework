<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\UnitTest\Definition;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Container\Compiler\Definition as DefinitionContract;
use Viserio\Component\Contract\Container\Exception\InvalidArgumentException;

/**
 * @internal
 */
abstract class AbstractDefinitionTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Contract\Container\Compiler\Definition
     */
    protected $definition;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var string
     */
    protected $name;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->value      = $this->getValue();
        $this->name       = $this->getDefinitionName();
        $this->definition = $this->getDefinition();
    }

    public function testGetName(): void
    {
        $this->assertSame($this->name, $this->definition->getName());
    }

    public function testGetValue(): void
    {
        $this->assertSame($this->value, $this->definition->getValue());
    }

    public function testIsShared(): void
    {
        $this->assertTrue($this->definition->isShared());
    }

    public function testIsLazy(): void
    {
        $this->assertFalse($this->definition->isLazy());

        $this->definition->setLazy(true);

        $this->assertTrue($this->definition->isLazy());
    }

    public function testAddExtender(): void
    {
        $this->definition->addExtender(function ($container, $value) {
            return $value;
        });

        $this->assertTrue($this->definition->isExtended());
    }

    public function testDeprecated(): void
    {
        $this->definition->setDeprecated();

        $this->assertSame('The [test] binding is deprecated. You should stop using it, as it will soon be removed.', $this->definition->getDeprecationMessage());
        $this->assertTrue($this->definition->isDeprecated());

        $this->definition->setDeprecated(true, '[%s]');

        $this->assertSame('[test]', $this->definition->getDeprecationMessage());
    }

    public function testSetDeprecatedThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The deprecation template must contain the [%s] placeholder.');

        $this->definition->setDeprecated(false, $this->name);
    }

    abstract protected function getDefinition(): DefinitionContract;

    abstract protected function getValue();

    abstract protected function getDefinitionName(): string;
}
