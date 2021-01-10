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

namespace Viserio\Component\Container\Tests\Integration\LazyProxy;

use PHPUnit\Framework\TestCase;
use ProxyManager\Version;
use stdClass;
use Viserio\Component\Container\Definition\ClosureDefinition;
use Viserio\Component\Container\Definition\ObjectDefinition;
use Viserio\Component\Container\Definition\ParameterDefinition;
use Viserio\Component\Container\LazyProxy\ProxyDumper;
use Viserio\Component\Container\Tests\Fixture\EmptyClass;
use Viserio\Component\Container\Tests\Fixture\Proxy\DummyInterface;
use Viserio\Component\Container\Tests\Fixture\Proxy\FinalDummyClass;
use Viserio\Component\Container\Tests\Fixture\Proxy\SunnyInterface;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

/**
 * Based on the Symfony ProxyManager Bridge.
 *
 * @see https://github.com/symfony/symfony/blob/4.3/src/Symfony/Bridge/ProxyManager/Tests/LazyProxy/PhpDumper/ProxyDumperTest.php
 *
 * @author Nicolas Grekas <p@tchwork.com>
 * @copyright Copyright (c) 2004-2017 Fabien Potencier
 *
 * @internal
 *
 * @covers \Viserio\Component\Container\LazyProxy\ProxyDumper
 *
 * @small
 */
final class ProxyDumperTest extends TestCase
{
    protected ProxyDumper $dumper;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->dumper = new ProxyDumper();
    }

    /**
     * @dataProvider provideIsProxyCandidateCases
     */
    public function testIsProxyCandidate(DefinitionContract $definition, bool $expected): void
    {
        self::assertSame($expected, $this->dumper->isSupported($definition));
    }

    public function testGetProxyCode(): void
    {
        $definition = new ObjectDefinition(__CLASS__, EmptyClass::class, 1);

        $definition->setLazy(true);

        $code = $this->dumper->getProxyCode($definition);

        self::assertStringMatchesFormat(
            '%Aclass EmptyClass%aextends%w'
            . '\Viserio\Component\Container\Tests\Fixture\EmptyClass%a',
            $code
        );
    }

    public function testDeterministicProxyCode(): void
    {
        $definition = new ObjectDefinition(__CLASS__, EmptyClass::class, 1);
        $definition->setLazy(true);

        self::assertSame($this->dumper->getProxyCode($definition), $this->dumper->getProxyCode($definition));
    }

    public function testGetProxyFactoryCode(): void
    {
        $definition = new ObjectDefinition('foo', $this, 1);
        $definition->setLazy(true);

        $code = $this->dumper->getProxyFactoryCode($definition, '$wrappedInstance = $this->getFoo2Service(false);');

        self::assertStringMatchesFormat(
            '%A$wrappedInstance = $this->getFoo2Service(false);%w$proxy->setProxyInitializer(null);%A',
            $code
        );
    }

    /**
     * @dataProvider provideCorrectAssigningCases
     */
    public function testCorrectAssigning(ObjectDefinition $definition, $access): void
    {
        $definition->setLazy(true);

        $code = $this->dumper->getProxyFactoryCode($definition, '$wrappedInstance = new EmptyClass();');

        self::assertStringMatchesFormat('%A$this->' . $access . '[\'' . $definition->getName() . '\'] = %A', $code);
    }

    public static function provideCorrectAssigningCases(): iterable
    {
        return [
            [
                (new ObjectDefinition('foo', EmptyClass::class, 2))
                    ->setPublic(false),
                'privates',
            ],
            [
                (new ObjectDefinition('foo', EmptyClass::class, 2))
                    ->setPublic(true),
                'services',
            ],
        ];
    }

    public function testGetProxyFactoryCodeWithoutCustomMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing factory code to construct the service [foo].');

        $definition = new ObjectDefinition('foo', EmptyClass::class, 1);
        $definition->setLazy(true);

        $this->dumper->getProxyFactoryCode($definition, '');
    }

    public function testGetProxyFactoryCodeForInterface(): void
    {
        $class = FinalDummyClass::class;
        $definition = new ObjectDefinition('foo', $class, 2);

        $definition->setLazy(true);
        $definition->addTag('proxy', ['interface' => DummyInterface::class]);
        $definition->addTag('proxy', ['interface' => SunnyInterface::class]);

        $implem = "<?php\n\n" . $this->dumper->getProxyCode($definition);
        $proxyFactory = $this->dumper->getProxyFactoryCode($definition, "                \$wrappedInstance = new \\Viserio\\Component\\Container\\Tests\\Fixture\\Proxy\\FinalDummyClass();\n");
        $factory = <<<EOPHP
<?php

return new class
{
    public \$proxyClass;
    private \$privates = [];

    public function get361043af246e7c8166dac3d9521cd38ed69a8d2f9075493f181c581560d7b289()
    {
{$proxyFactory}
    }

    protected function createProxy(\$class, \\Closure \$factory)
    {
        \$this->proxyClass = \$class;

        return \$factory();
    }
};

EOPHP;

        $implem = \preg_replace('#\n    /\*\*.*?\*/#s', '', $implem);
        $implem = \str_replace("array(\n        \n    );", "[\n        \n    ];", $implem);
        $implem = \preg_replace('/valueHolder[a-zA-Z0-9]+/m', 'valueHolder%s', $implem);
        $implem = \preg_replace('/publicProperties[a-zA-Z0-9]+/m', 'publicProperties%s', $implem);
        $implem = \preg_replace('/initializer[a-zA-Z0-9]+/m', 'initializer%s', $implem);

        $implemPath = __DIR__ . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'Proxy' . \DIRECTORY_SEPARATOR . 'proxy-implem.php';
        $proxyPath = __DIR__ . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'Proxy' . \DIRECTORY_SEPARATOR . 'proxy-factory.php';

        self::assertStringMatchesFormat($implem, \file_get_contents($implemPath));
        self::assertStringEqualsFile($proxyPath, $factory);

        require_once $implemPath;

        $factory = require $proxyPath;

        /** @var \Viserio\Component\Container\Tests\Fixture\Proxy\FinalDummyClass $foo */
        $foo = $factory->get361043af246e7c8166dac3d9521cd38ed69a8d2f9075493f181c581560d7b289();

        $class = \get_class($foo);
        $interfaces = \class_implements($foo);

        self::assertSame($factory->proxyClass, $class);
        self::assertNotSame(FinalDummyClass::class, $class);
        self::assertContains(DummyInterface::class, $interfaces);
        self::assertContains(SunnyInterface::class, $interfaces);
        self::assertSame($foo, $foo->dummy());

        $foo->dynamicProp = 123;

        self::assertSame(123, @$foo->dynamicProp);
    }

    public static function provideIsProxyCandidateCases(): iterable
    {
        $definitions = [
            [new ObjectDefinition(__CLASS__, EmptyClass::class, 1), true],
            [new ObjectDefinition('stdClass', new stdClass(), 1), true],
            [new ParameterDefinition('foo', \uniqid('foo', true)), false],
            [new ClosureDefinition('foo', function (): void {
            }, 1), false],
        ];

        \array_map(
            static function ($definition): void {
                $definition[0]->setLazy(true);
            },
            $definitions
        );

        return $definitions;
    }

    public function testStaticBinding(): void
    {
        if (! \class_exists(Version::class) || \version_compare(\defined(Version::class . '::VERSION') ? Version::VERSION : Version::getVersion(), '2.1', '<')) {
            self::markTestSkipped('ProxyManager prior to version 2.1 does not support static binding');
        }

        $definition = new ObjectDefinition(__CLASS__, TestCase::class, 1);
        $definition->setLazy(true);

        $code = $this->dumper->getProxyCode($definition);

        self::assertStringContainsString('\Closure::bind(function (\PHPUnit\Framework\TestCase $instance) {', $code);
    }
}
