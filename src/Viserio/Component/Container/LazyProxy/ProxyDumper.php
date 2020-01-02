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

namespace Viserio\Component\Container\LazyProxy;

use ProxyManager\GeneratorStrategy\BaseGeneratorStrategy;
use ProxyManager\Version;
use ReflectionClass;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Definition\ObjectDefinition;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Contract\Container\LazyProxy\Dumper as DumperContract;
use Zend\Code\Generator\ClassGenerator;

/**
 * Based on the Symfony ProxyManager Bridge.
 *
 * @see https://github.com/symfony/symfony/blob/4.3/src/Symfony/Bridge/ProxyManager/LazyProxy/PhpDumper/ProxyDumper.php
 *
 * @author Nicolas Grekas <p@tchwork.com>
 * @copyright Copyright (c) 2004-2017 Fabien Potencier
 */
class ProxyDumper implements DumperContract
{
    /**
     * A BaseGeneratorStrategy instance.
     *
     * @var \ProxyManager\GeneratorStrategy\BaseGeneratorStrategy
     */
    private $strategy;

    /**
     * A LazyLoadingValueHolderGenerator instance.
     *
     * @var \Viserio\Component\Container\LazyProxy\LazyLoadingValueHolderGenerator
     */
    private $proxyGenerator;

    /**
     * Create a new ProxyDumper instance.
     */
    public function __construct()
    {
        $this->strategy = new BaseGeneratorStrategy();
        $this->proxyGenerator = new LazyLoadingValueHolderGenerator();
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported(DefinitionContract $definition): bool
    {
        return $definition instanceof ObjectDefinition && $definition->isLazy();
    }

    /**
     * {@inheritdoc}
     */
    public function getProxyCode(ObjectDefinitionContract $definition): string
    {
        $generator = $this->generateProxyClass($definition);
        $reflection = null;

        if (($extentedClass = $generator->getExtendedClass()) !== null) {
            $reflection = new ReflectionClass($extentedClass);
        }

        $code = $this->strategy->generate($generator);

        // fix for internal class extend
        $code = \preg_replace('/^(class [^ ]++ extends )([^\\\\])/', '$1\\\\$2', $code);

        if (\version_compare(self::getProxyManagerVersion(), '2.5', '<')) {
            $code = \preg_replace('/ \\\\Closure::bind\(function ((?:& )?\(\$instance(?:, \$value)?\))/', ' \Closure::bind(static function \1', $code);
        }

        return $code;
    }

    /**
     * {@inheritdoc}
     */
    public function getProxyFactoryCode(ObjectDefinitionContract $definition, string $factoryCode): string
    {
        $id = $definition->getName();

        $instantiation = 'return';

        if ($definition->isShared()) {
            $instantiation .= \sprintf(' $this->%s[%s] =', $definition->isPublic() ? 'services' : 'privates', \var_export($id, true));
        }

        if ($factoryCode === '') {
            throw new InvalidArgumentException(\sprintf('Missing factory code to construct the service [%s].', $id));
        }

        $proxyClass = $this->getProxyClassName($definition);
        $static = \is_int(\strpos($factoryCode, '$this')) ? '' : 'static ';
        $eol = "\n";

        return "        {$instantiation} \$this->createProxy('{$proxyClass}', {$static}function () {{$eol}            return {$proxyClass}::staticProxyConstructor({$static}function (&\$wrappedInstance, \\ProxyManager\\Proxy\\LazyLoadingInterface \$proxy) {{$eol}{$factoryCode}{$eol}                \$proxy->setProxyInitializer(null);{$eol}{$eol}                return true;{$eol}            });{$eol}        });";
    }

    /**
     * @param \Viserio\Contract\Container\Definition\ObjectDefinition $definition
     *
     * @return \Zend\Code\Generator\ClassGenerator
     */
    private function generateProxyClass(ObjectDefinitionContract $definition): ClassGenerator
    {
        $generatedClass = new ClassGenerator($this->getProxyClassName($definition));

        $class = $this->proxyGenerator->getProxifiedClass($definition);

        $this->proxyGenerator->setFluentSafe($definition->hasTag('proxy'));
        $this->proxyGenerator->generate(new ReflectionClass($class), $generatedClass);

        return $generatedClass;
    }

    /**
     * @return string
     */
    private static function getProxyManagerVersion(): string
    {
        return \defined(Version::class . '::VERSION') ? Version::VERSION : Version::getVersion();
    }

    /**
     * Produces the proxy class name for the given definition.
     *
     * @param \Viserio\Contract\Container\Definition\ObjectDefinition $definition
     *
     * @return string
     */
    private function getProxyClassName(ObjectDefinitionContract $definition): string
    {
        $class = $this->proxyGenerator->getProxifiedClass($definition);

        return \preg_replace('/^.*\\\\/', '', $class) . '_' . $this->getIdentifierSuffix($definition);
    }

    /**
     * @param \Viserio\Contract\Container\Definition\ObjectDefinition $definition
     *
     * @return string
     */
    private function getIdentifierSuffix(ObjectDefinitionContract $definition): string
    {
        return ContainerBuilder::getHash($this->proxyGenerator->getProxifiedClass($definition));
    }
}
