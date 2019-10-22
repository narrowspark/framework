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

namespace Viserio\Bridge\Phpstan\Type\Viserio;

use Closure;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
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
use Viserio\Contract\Container\Definition\ClosureDefinition as ClosureDefinitionContract;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Container\Definition\FactoryDefinition as FactoryDefinitionContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\Definition\UndefinedDefinition as UndefinedDefinitionContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;

class ContainerBuilderTypeExtension implements DynamicMethodReturnTypeExtension
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

        if ($parameter instanceof New_ && $parameter->class instanceof Class_) {
            $type = ObjectDefinitionContract::class;
        } elseif ($parameter instanceof ClassConstFetch && $parameter->class instanceof Name) {
            $type = $this->resolveClassDefinition($parameter);
        } elseif ($parameter instanceof Array_) {
            if (\count($parameter->items) === 2 && $parameter->items[0] instanceof ArrayItem && $parameter->items[1] instanceof ArrayItem && $parameter->items[1]->value instanceof String_ && ($parameter->items[0]->value instanceof ClassConstFetch || $parameter->items[0]->value instanceof New_) && $parameter->items[0]->value->class instanceof Name) {
                $type = FactoryDefinitionContract::class;
            } else {
                $type = DefinitionContract::class;
            }
        } elseif ($parameter instanceof FuncCall) {
            $type = FactoryDefinitionContract::class;
        } elseif ($parameter instanceof Closure_) {
            $type = ClosureDefinitionContract::class;
        }

        return new ObjectType($type);
    }

    /**
     * @param \PhpParser\Node\Expr\ClassConstFetch $parameter
     *
     * @throws \ReflectionException
     *
     * @return string
     */
    private function resolveClassDefinition(ClassConstFetch $parameter): string
    {
        $classReflection = new ReflectionClass($parameter->class->toString());

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
