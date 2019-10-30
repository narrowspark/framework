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

use ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator as BaseGenerator;
use ReflectionClass;
use UnexpectedValueException;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;
use Zend\Code\Generator\ClassGenerator;

/**
 * @internal
 *
 * Based on the Symfony ProxyManager Bridge
 *
 * @see https://github.com/symfony/symfony/blob/4.3/src/Symfony/Bridge/ProxyManager/LazyProxy/PhpDumper/LazyLoadingValueHolderGenerator.php
 *
 * @author Nicolas Grekas <p@tchwork.com>
 * @copyright Copyright (c) 2004-2017 Fabien Potencier
 */
final class LazyLoadingValueHolderGenerator extends BaseGenerator
{
    /** @var bool */
    private $fluentSafe = false;

    /**
     * @param bool $fluentSafe
     *
     * @return void
     */
    public function setFluentSafe(bool $fluentSafe): void
    {
        $this->fluentSafe = $fluentSafe;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator): void
    {
        parent::generate($originalClass, $classGenerator);

        foreach ($classGenerator->getMethods() as $method) {
            $body = \preg_replace(
                '/(\$this->initializer[0-9a-f]++) && \1->__invoke\(\$this->(valueHolder[0-9a-f]++), (.*?), \1\);/',
                '$1 && ($1->__invoke(\$$2, $3, $1) || 1) && $this->$2 = \$$2;',
                $method->getBody()
            );

            $body = \str_replace(['(new \ReflectionClass(get_class()))', '$reflection = $reflection ?: ', '$reflection ?? $reflection = '], ['$reflection', '$reflection = $reflection ?? ', '$reflection ?? '], $body);

            if ($originalClass->isInterface()) {
                $body = \str_replace('get_parent_class($this)', \var_export($originalClass->name, true), $body);
                $body = \preg_replace_callback('/\n\n\$realInstanceReflection = [^{]++\{([^}]++)\}\n\n.*/s', function ($m) {
                    $r = '';

                    foreach (\explode("\n", $m[1]) as $line) {
                        $r .= "\n" . \substr($line, 4);

                        if (0 === \strpos($line, '    return ')) {
                            break;
                        }
                    }

                    return $r;
                }, $body);
            }

            if ($this->fluentSafe) {
                $indent = $method->getIndentation();

                $method->setIndentation('');

                $code = $method->generate();

                if (null !== $docBlock = $method->getDocBlock()) {
                    $code = \substr($code, \strlen($docBlock->generate()));
                }

                $refAmp = (\strpos($code, '&') ?: \PHP_INT_MAX) <= \strpos($code, '(') ? '&' : '';
                $body = \preg_replace(
                    '/\nreturn (\$this->valueHolder[0-9a-f]++)(->[^;]++);$/',
                    "\nif ($1 === \$returnValue = {$refAmp}$1$2) {\n    \$returnValue = \$this;\n}\n\nreturn \$returnValue;",
                    $body
                );

                $method->setIndentation($indent);
            }

            if ($originalClass->getFileName() !== false && \strpos($originalClass->getFileName(), __FILE__) === 0) {
                $body = \str_replace(\var_export($originalClass->name, true), '__CLASS__', $body);
            }

            $method->setBody($body);
        }

        if ($classGenerator->hasMethod('__destruct')) {
            $destructor = $classGenerator->getMethod('__destruct');

            if ($destructor !== false) {
                $body = $destructor->getBody();
                $newBody = \preg_replace('/^(\$this->initializer[a-zA-Z0-9]++) && .*;\n\nreturn (\$this->valueHolder)/', '$1 || $2', $body);

                if ($body === $newBody) {
                    throw new UnexpectedValueException(\sprintf('Unexpected lazy-proxy format generated for method %s::__destruct()', $originalClass->name));
                }

                $destructor->setBody($newBody);
            }
        }

        if ($originalClass->getFileName() !== false && \strpos($originalClass->getFileName(), __FILE__) === 0) {
            $interfaces = $classGenerator->getImplementedInterfaces();

            \array_pop($interfaces);

            $classGenerator->setImplementedInterfaces(\array_merge($interfaces, $originalClass->getInterfaceNames()));
        }
    }

    /**
     * Get proxified class FQN.
     *
     * @param \Viserio\Contract\Container\Definition\ObjectDefinition $definition
     *
     * @return null|string
     */
    public function getProxifiedClass(ObjectDefinitionContract $definition): ?string
    {
        if (! $definition->hasTag('proxy')) {
            return \class_exists($class = $definition->getClass()) || \interface_exists($class, false) ? $class : null;
        }

        if (! $definition->isLazy()) {
            throw new InvalidArgumentException(\sprintf('Invalid definition for service of class [%s]: setting the "proxy" tag on a service requires it to be "lazy".', $definition->getClass()));
        }

        $tags = $definition->getTag('proxy');

        if (! isset($tags[0]['interface'])) {
            throw new InvalidArgumentException(\sprintf('Invalid definition for service of class [%s]: the "interface" attribute is missing on the "proxy" tag.', $definition->getClass()));
        }

        if (1 === \count($tags)) {
            return \class_exists($tags[0]['interface']) || \interface_exists($tags[0]['interface'], false) ? $tags[0]['interface'] : null;
        }

        $proxyInterface = 'LazyProxy';
        $interfaces = '';

        foreach ($tags as $tag) {
            if (! isset($tag['interface'])) {
                throw new InvalidArgumentException(\sprintf('Invalid definition for service of class [%s]: the "interface" attribute is missing on a "proxy" tag.', $definition->getClass()));
            }

            if (! \interface_exists($tag['interface'])) {
                throw new InvalidArgumentException(\sprintf('Invalid definition for service of class [%s]: several "proxy" tags found but [%s] is not an interface.', $definition->getClass(), $tag['interface']));
            }

            $proxyInterface .= '\\' . $tag['interface'];
            $interfaces .= ', \\' . $tag['interface'];
        }

        if (! \interface_exists($proxyInterface)) {
            $i = \strrpos($proxyInterface, '\\');
            $namespace = \substr($proxyInterface, 0, $i);
            $interface = \substr($proxyInterface, 1 + $i);
            $interfaces = \substr($interfaces, 2);

            eval("namespace {$namespace}; interface {$interface} extends {$interfaces} {}");
        }

        return $proxyInterface;
    }
}
