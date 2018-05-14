<?php
declare(strict_types=1);
namespace Viserio\Component\Container\LazyProxy;

use ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator as BaseGenerator;
use ReflectionClass;
use Zend\Code\Generator\ClassGenerator;

/**
 * @internal
 */
final class LazyLoadingValueHolderGenerator extends BaseGenerator
{
    /**
     * {@inheritdoc}
     */
    public function generate(ReflectionClass $originalClass, ClassGenerator $classGenerator): void
    {
        parent::generate($originalClass, $classGenerator);

        if ($classGenerator->hasMethod('__destruct')) {
            $destructor = $classGenerator->getMethod('__destruct');
            $body       = $destructor->getBody();
            $newBody    = \preg_replace('/^(\$this->initializer[a-zA-Z0-9]++) && .*;\n\nreturn (\$this->valueHolder)/', '$1 || $2', $body);

            if ($body === $newBody) {
                throw new \UnexpectedValueException(\sprintf('Unexpected lazy-proxy format generated for method %s::__destruct()', $originalClass->getName()));
            }

            $destructor->setBody($newBody);
        }
    }
}
