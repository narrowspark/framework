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

namespace Viserio\Bridge\Phpstan\Type\Viserio\Container;

use Closure;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Cast\Object_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure as Closure_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use ReflectionClass;
use ReflectionException;
use Viserio\Contract\Container\Definition\ClosureDefinition as ClosureDefinitionContract;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Container\Definition\FactoryDefinition as FactoryDefinitionContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\Definition\UndefinedDefinition as UndefinedDefinitionContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;

class DynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function getClass(): string
    {
        return ContainerBuilderContract::class;
    }

    /**
     * {@inheritdoc}
     */
    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return \in_array($methodReflection->getName(), ['bind', 'singleton'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): Type {
        $type = UndefinedDefinitionContract::class;

        $arg = $methodCall->args[1] ?? $methodCall->args[0];
        $parameter = $arg->value;

        if ($parameter instanceof New_) {
            if ($parameter->class instanceof Class_) {
                $type = ObjectDefinitionContract::class;
            } elseif ($parameter->class instanceof Name\FullyQualified) {
                $type = $this->resolveClassDefinition($parameter->class->toString());
            }
        } elseif ($parameter instanceof ClassConstFetch && $parameter->class instanceof Name) {
            $type = $this->resolveClassDefinition($parameter->class->toString());
        } elseif ($parameter instanceof Array_) {
            $isFactoryArray = \count($parameter->items) === 2 && isset($parameter->items[0], $parameter->items[1]) && $parameter->items[1]->value instanceof String_;

            $type = DefinitionContract::class;

            if ($isFactoryArray) {
                $class = $parameter->items[0]->value;

                if ((($class instanceof ClassConstFetch || $class instanceof New_) && $class->class instanceof Name)
                    || ($class instanceof String_ && class_exists($class->value, false))) {
                    $type = FactoryDefinitionContract::class;
                }
            }
        } elseif ($parameter instanceof FuncCall) {
            $type = FactoryDefinitionContract::class;
        } elseif ($parameter instanceof Closure_) {
            $type = ClosureDefinitionContract::class;
        } elseif ($parameter instanceof Concat && $parameter->left instanceof ClassConstFetch && $parameter->right instanceof String_ && \strpos($parameter->right->value, '::') !== false) {
            $type = FactoryDefinitionContract::class;
        } elseif ($parameter instanceof Object_ && $parameter->expr instanceof Array_) {
            $type = DefinitionContract::class;
        }

        return new ObjectType($type);
    }

    /**
     * Get the correct Definition Interface out of given class.
     *
     * @param string $class
     *
     * @return string
     */
    private function resolveClassDefinition(string $class): string
    {
        try {
            $classReflection = new ReflectionClass($class);
        } catch (ReflectionException $exception) {
            return UndefinedDefinitionContract::class;
        }

        if ($classReflection->isSubclassOf(Closure::class)) {
            $type = ClosureDefinitionContract::class;
        } elseif ($classReflection->isIterateable() && ! $classReflection->isUserDefined()) {
            $type = DefinitionContract::class;
        } else {
            $type = ObjectDefinitionContract::class;
        }

        return $type;
    }
}
