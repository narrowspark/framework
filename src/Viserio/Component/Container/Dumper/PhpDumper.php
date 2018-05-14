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

namespace Viserio\Component\Container\Dumper;

use Closure;
use EmptyIterator;
use Generator;
use Opis\Closure\ReflectionClosure;
use PhpParser\Error;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use SplObjectStorage;
use stdClass;
use Viserio\Component\Container\AbstractCompiledContainer;
use Viserio\Component\Container\Argument\ArrayArgument;
use Viserio\Component\Container\Argument\ClosureArgument;
use Viserio\Component\Container\Argument\IteratorArgument;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Definition\AliasDefinition;
use Viserio\Component\Container\Definition\ArrayDefinition;
use Viserio\Component\Container\Definition\ClosureDefinition;
use Viserio\Component\Container\Definition\ConditionDefinition;
use Viserio\Component\Container\Definition\FactoryDefinition;
use Viserio\Component\Container\Definition\IteratorDefinition;
use Viserio\Component\Container\Definition\ObjectDefinition;
use Viserio\Component\Container\Definition\ParameterDefinition;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\PhpParser\NodeVisitor\AnonymousClassLocatorVisitor;
use Viserio\Component\Container\PhpParser\NodeVisitor\ClosureLocatorVisitor;
use Viserio\Component\Container\PhpParser\NodeVisitor\MagicConstantVisitor;
use Viserio\Component\Container\PhpParser\NodeVisitor\ThisDetectorVisitor;
use Viserio\Component\Container\PhpParser\NodeVisitor\UsesCollectorNodeVisitor;
use Viserio\Component\Container\Pipeline\AnalyzeServiceDependenciesPipe;
use Viserio\Component\Container\Pipeline\CheckCircularReferencesPipe;
use Viserio\Component\Container\Variable;
use Viserio\Contract\Container\Argument\Argument as ArgumentContract;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\ArgumentAwareDefinition as ArgumentAwareDefinitionContract;
use Viserio\Contract\Container\Definition\ClosureDefinition as ClosureDefinitionContract;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Container\Definition\FactoryDefinition as FactoryDefinitionContract;
use Viserio\Contract\Container\Definition\MethodCallsAwareDefinition as MethodCallsAwareDefinitionContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\Definition\PropertiesAwareDefinition as PropertiesAwareDefinitionContract;
use Viserio\Contract\Container\Definition\ReferenceDefinition as ReferenceDefinitionContract;
use Viserio\Contract\Container\Definition\UndefinedDefinition as UndefinedDefinitionContract;
use Viserio\Contract\Container\Dumper\Dumper as DumperContract;
use Viserio\Contract\Container\Exception\CircularDependencyException;
use Viserio\Contract\Container\Exception\CompileException;
use Viserio\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Contract\Container\Exception\LogicException;
use Viserio\Contract\Container\Exception\RuntimeException;
use Viserio\Contract\Container\LazyProxy\Dumper as LazyProxyContract;
use Viserio\Contract\Container\ServiceReferenceGraphNode as ServiceReferenceGraphNodeContract;
use Viserio\Contract\Support\Exception\MissingPackageException;

final class PhpDumper implements DumperContract
{
    /**
     * List of reserved variables.
     *
     * @var array
     */
    private static $reservedVariables = ['instance', 'class', 'this'];

    /**
     * Cache for preload tag check.
     *
     * @var array
     */
    private static $preloadCache = [];

    /**
     * A container builder instance.
     *
     * @var \Viserio\Contract\Container\ContainerBuilder
     */
    private $containerBuilder;

    /**
     * A php parser instance.
     *
     * @var null|\PhpParser\Parser
     */
    private $phpParser;

    /**
     * A php pretty printer instance.
     *
     * @var null|\PhpParser\PrettyPrinter\Standard
     */
    private $printer;

    /** @var array */
    private $inlinedRequires = [];

    /**
     * Counted variable.
     *
     * @var int
     */
    private $variableCount = 0;

    /**
     * Array space counter for multidimensional arrays.
     *
     * @var int
     */
    private $arraySpaceCount = 2;

    /**
     * A check if the container should be a file container.
     *
     * @var bool
     */
    private $asFiles = false;

    /** @var null|\SplObjectStorage */
    private $inlinedDefinitions;

    /** @var \SplObjectStorage */
    private $definitionVariables;

    /** @var array */
    private $circularReferences = [];

    /** @var array */
    private $singleUsePrivateIds = [];

    /** @var array */
    private $services = [];

    /**
     * List of uninitialized references.
     *
     * @var array
     */
    private $uninitializedServices = [];

    /**
     * Check if container should be dump in debug mode.
     *
     * @var bool
     */
    private $debug = false;

    /** @var bool */
    private $inlineRequires = false;

    /** @var bool */
    private $inlineFactories = false;

    /** @var null|array */
    private $referenceVariables;

    /** @var null|array */
    private $serviceCalls;

    /** @var null|string */
    private $targetDirRegex;

    /** @var null|array */
    private $targetDirMaxMatches;

    /**
     * A lazy proxy dumper.
     *
     * @var \Viserio\Contract\Container\LazyProxy\Dumper
     */
    private $proxyDumper;

    /**
     * Tag that identifies the services that are always needed.
     *
     * @var string
     */
    private $preloadTag;

    /** @var bool */
    private $wrapConditionCalled = false;

    /**
     * Create a new PhpDumper instance.
     *
     * @param \Viserio\Contract\Container\ContainerBuilder $container
     * @param null|\PhpParser\Parser                       $phpParser
     * @param null|\PhpParser\PrettyPrinter\Standard       $printer
     */
    public function __construct(ContainerBuilderContract $container, Parser $phpParser = null, Standard $printer = null)
    {
        if (! $container->isCompiled()) {
            throw new LogicException('Cannot dump an uncompiled container.');
        }

        $this->containerBuilder = $container;
        $this->phpParser = $phpParser;
        $this->printer = $printer;
    }

    /**
     * Sets the dumper to be used when dumping proxies in the generated container.
     *
     * @param \Viserio\Contract\Container\LazyProxy\Dumper $proxyDumper
     *
     * @return \Viserio\Contract\Container\Dumper\Dumper
     */
    public function setProxyDumper(LazyProxyContract $proxyDumper): DumperContract
    {
        $this->proxyDumper = $proxyDumper;

        return $this;
    }

    /**
     * Helps to write generated container code to file.
     *
     * @param string $fileName
     * @param string $fileContent
     *
     * @return bool
     */
    public static function dumpCodeToFile(string $fileName, string $fileContent): bool
    {
        self::createCompilationDirectory(\dirname($fileName));

        $successful = \file_put_contents($fileName, $fileContent);

        if ($successful === false) {
            throw new RuntimeException(\sprintf('Failed to write file [%s].', $fileName));
        }

        @\chmod($fileName, 0666 & ~\umask());

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $options = [])
    {
        $options = \array_merge([
            'base_class' => AbstractCompiledContainer::class,
            'build_time' => \time(),
            'class' => 'Container',
            'debug' => false,
            'file' => null,
            'namespace' => null,
            'preload_tag' => 'container.preload',
            'as_files_parameter' => 'container.dumper.as_files',
            'inline_class_loader_parameter' => 'container.dumper.inline_class_loader',
            'inline_factories_parameter' => 'container.dumper.inline_factories',
        ], $options);

        $this->validateDumperOptions($options);

        $this->debug = $options['debug'];
        $this->preloadTag = $options['preload_tag'];
        $this->asFiles = \is_string($options['as_files_parameter']) && $this->containerBuilder->hasParameter($options['as_files_parameter']) ? (bool) $this->containerBuilder->getParameter($options['as_files_parameter'])->getValue() : false;
        $this->inlineRequires = \is_string($options['inline_class_loader_parameter']) && $this->containerBuilder->hasParameter($options['inline_class_loader_parameter']) && (bool) $this->containerBuilder->getParameter($options['inline_class_loader_parameter'])->getValue();
        $this->inlineFactories = \is_string($options['inline_factories_parameter']) && $this->containerBuilder->hasParameter($options['inline_factories_parameter']) && (bool) $this->containerBuilder->getParameter($options['inline_factories_parameter'])->getValue();

        $class = \ltrim($options['class'], '\\');
        $parentClass = \ltrim($options['base_class'], '\\');

        // Validate that a valid class name was provided
        if (! \preg_match('/^[a-zA-Z_]\w*$/', $class)) {
            throw new InvalidArgumentException(\sprintf('The container cannot be compiled: [%s] is not a valid PHP class name.', $class));
        }

        $hasProxyDumper = $this->proxyDumper !== null;

        if (! $hasProxyDumper) {
            (new AnalyzeServiceDependenciesPipe(true, false))->process($this->containerBuilder);

            try {
                (new CheckCircularReferencesPipe())->process($this->containerBuilder);
            } catch (CircularDependencyException $exception) {
                throw new CircularDependencyException($exception->getClass(), $exception->getBuildStack(), null, '"Try running "composer require ocramius/proxy-manager');
            }
        }

        (new AnalyzeServiceDependenciesPipe(false, ! $hasProxyDumper))->process($this->containerBuilder);

        $checkedNodes = $this->circularReferences = $this->singleUsePrivateIds = [];

        foreach ($this->containerBuilder->getServiceReferenceGraph()->getNodes() as $id => $node) {
            if (! $node->getValue() instanceof DefinitionContract) {
                continue;
            }

            if (! \array_key_exists($id, $checkedNodes)) {
                $this->analyzeCircularReferences($id, $node->getOutEdges(), $checkedNodes);
            }

            if ($this->isSingleUsePrivateNode($node)) {
                $this->singleUsePrivateIds[$id] = $id;
            }
        }

        $this->containerBuilder->getServiceReferenceGraph()->reset();
        unset($checkedNodes); // reset

        if ($options['file'] !== null && \is_dir($dir = \dirname($options['file']))) {
            // Build a regexp where the first root dirs are mandatory,
            // but every other sub-dir is optional up to the full path in $dir
            // Mandate at least 2 root dirs and not more that 5 optional dirs.
            $dir = \explode(\DIRECTORY_SEPARATOR, \realpath($dir));
            $i = \count($dir);

            if (3 <= $i) {
                $regex = '';
                $lastOptionalDir = $i > 8 ? $i - 5 : 3;
                $this->targetDirMaxMatches = $i - $lastOptionalDir;

                while (--$i >= $lastOptionalDir) {
                    $regex = \sprintf('(%s%s)?', \preg_quote(\DIRECTORY_SEPARATOR . $dir[$i], '#'), $regex);
                }

                do {
                    $regex = \preg_quote(\DIRECTORY_SEPARATOR . $dir[$i], '#') . $regex;
                } while (0 < --$i);

                $this->targetDirRegex = '#' . \preg_quote($dir[0], '#') . $regex . '#';
            }
        }

        $proxyClasses = $this->inlineFactories ? $this->generateProxyClasses() : null;
        $servicesContent = $this->addServices();

        $this->phpParser = $this->printer = null;

        $classContent = $this->getClassStartForCompiledClass($class, $parentClass, $options['namespace'])
            . $servicesContent
            . $this->addDeprecatedAliases()
            . $this->addRemovedIds();

        $classEndCode = $this->getClassEndForCompiledClass($options['namespace']);
        $proxyClasses = $proxyClasses ?? $this->generateProxyClasses();

        if ($this->asFiles) {
            $code = $this->generateFileContainer($options, $proxyClasses, $classContent, $classEndCode);
        } else {
            $code = $classContent . $classEndCode;

            foreach ($proxyClasses as $c) {
                $code .= $c;
            }
        }

        $this->inlinedRequires = $this->singleUsePrivateIds = $this->services = $this->circularReferences = [];
        $this->targetDirRegex = $this->proxyDumper = $this->containerBuilder = $this->inlinedDefinitions = null;
        $this->definitionVariables = $this->referenceVariables = $this->serviceCalls = $this->targetDirRegex = $this->targetDirMaxMatches = null;
        $this->containerBuilder = null;
        $this->variableCount = 0;

        return $code;
    }

    /**
     * Compiling the parameters to a protected parameter variable for the container.
     *
     * @return string
     */
    private function addParameters(): string
    {
        $eol = "\n";
        $code = $this->compileParameters($this->containerBuilder->getParameters());

        if ($code === '[]') {
            return '';
        }

        if ($this->asFiles) {
            return \sprintf("{$eol}        \$this->parameters = \\array_merge(%s, \$buildParameters);", $code);
        }

        return \sprintf("{$eol}        \$this->parameters = %s;", $code);
    }

    /**
     * Compiling container definitions.
     *
     *@throws \ReflectionException
     * @throws \Viserio\Contract\Container\Exception\NotFoundException
     * @throws \Viserio\Contract\Container\Exception\CircularDependencyException
     *
     * @return string
     */
    private function addServices(): string
    {
        $publicBindings = $privateBindings = '';

        $definitions = $this->containerBuilder->getDefinitions();

        \ksort($definitions);

        foreach ($definitions as $id => $definition) {
            $this->isPreload($definition);

            $this->services[$id] = $definition->isSynthetic() ? null : $this->addService($id, $definition);
        }

        foreach ($definitions as $id => $definition) {
            if (\array_key_exists($id, $this->services)) {
                [$file, $code] = $this->services[$id];

                if ($file !== null) {
                    continue;
                }

                if ($definition->isPublic()) {
                    $publicBindings .= $code;
                } elseif (! $this->isTrivialInstance($definition)) {
                    $privateBindings .= $code;
                }
            }
        }

        return $publicBindings . $privateBindings;
    }

    /**
     * Compiling the deprecated aliases.
     *
     * @return string
     */
    private function addDeprecatedAliases(): string
    {
        $code = '';
        $eol = "\n";
        $aliases = $this->containerBuilder->getAliases();

        \ksort($aliases);

        foreach ($aliases as $alias => $definition) {
            if (! $definition->isDeprecated()) {
                continue;
            }

            $public = $definition->isPublic() ? 'public' : 'private';

            $code .= "{$eol}{$eol}    /**{$eol}";
            $code .= \sprintf("     * Gets the %s \"%s\" alias.{$eol}     *{$eol}", $public, $alias);
            $code .= \sprintf("     * @return mixed The \"%s\" service.{$eol}     */{$eol}", $definition->getName());
            $code .= \sprintf("    protected function get%s(){$eol}    {{$eol}", $definition->getHash());
            $code .= \sprintf("        @\\trigger_error('%s', \\E_USER_DEPRECATED);{$eol}{$eol}        return \$this->get(%s);{$eol}    }", $definition->getDeprecationMessage(), $this->compileValue($definition->getName()));
        }

        return $code;
    }

    /**
     * Dump method with doc header and inline services.
     *
     * @param string                                                                                                                      $id
     * @param ClosureDefinitionContract|DefinitionContract|FactoryDefinitionContract|ObjectDefinitionContract|UndefinedDefinitionContract $definition
     *
     *@throws \Viserio\Contract\Container\Exception\CircularDependencyException
     * @throws \Viserio\Contract\Container\Exception\NotFoundException
     * @throws \ReflectionException
     *
     * @return array
     */
    private function addService(string $id, $definition): array
    {
        $arraySpaceCount = $this->arraySpaceCount;

        if ($this->asFiles && ! $this->inlineFactories && ! $this->isPreload($definition)) {
            $this->arraySpaceCount = 0;
        }

        $eol = "\n";
        $this->definitionVariables = new SplObjectStorage();
        $this->referenceVariables = [];
        $this->variableCount = 0;
        $this->referenceVariables[$id] = new Variable('instance');

        $asFile = $definition->isShared() && $this->asFiles && ! $this->inlineFactories && ! $this->isPreload($definition);
        $doc = "{$eol}{$eol}    /**";
        $doc .= \sprintf((! $asFile ? $eol : '') . '     * Returns the %s %s%s service.' . (! $asFile ? "{$eol}     *" : $eol . $eol), $definition->isPublic() ? 'public' : 'private', $id, $definition->isShared() ? ' shared' : '');
        $returnType = null;

        if (! $asFile) {
            if ($definition instanceof ObjectDefinitionContract) {
                $class = $definition->getClass();

                if (is_anonymous_class($class)) {
                    $class = 'object';
                    $returnType = 'object';
                } else {
                    $returnType = $this->generateLiteralClass($class);
                }

                $doc .= \sprintf(\strpos($class, '%') === 0 ? '%s@return object A %1$s instance' : '%s@return %s', "{$eol}     * ", $returnType);
            } elseif ($definition instanceof FactoryDefinitionContract) {
                $returnType = $definition->getReturnType();

                $doc .= "{$eol}     * " . \sprintf('@return %s An instance returned by %s::%s()', $definition->getReturnType() ?? 'mixed', $this->generateLiteralClass($definition->getClass()), $definition->getMethod());
            } elseif ($definition instanceof ClosureDefinition) {
                $doc .= "{$eol}     * @return mixed Returned by a function";
            } elseif ($definition instanceof ArrayDefinition) {
                $returnType = 'array';

                $doc .= "{$eol}     * @return array";
            } elseif ($definition instanceof IteratorDefinition) {
                $returnType = '\Viserio\Component\Container\RewindableGenerator';

                $doc .= "{$eol}     * @return \\Viserio\\Component\\Container\\RewindableGenerator";
            }
        }

        $isDeprecated = $definition->isDeprecated();

        if ($isDeprecated) {
            $doc .= \sprintf("{$eol}     *{$eol}     * @deprecated %s{$eol}", $definition->getDeprecationMessage());
        } else {
            $doc .= $eol;
        }

        $file = null;
        $code = '';
        $methodName = 'get' . $definition->getHash();

        if ($asFile) {
            $file = "{$methodName}.php";
            $code = \sprintf("{$eol}// Returns the %s %s%s service.{$eol}{$eol}", $definition->isPublic() ? 'public' : 'private', $id, $definition->isShared() ? ' shared' : '');
        }

        $this->serviceCalls = [];
        $this->inlinedDefinitions = $this->getDefinitionsFromArguments([$definition], null, $this->serviceCalls);

        $isProxy = $this->proxyDumper !== null && $this->proxyDumper->isSupported($definition);

        $code .= $this->addServiceInclude($definition, $isProxy);

        if ($isDeprecated) {
            $code .= \sprintf("%s@\\trigger_error('%s', \\E_USER_DEPRECATED);{$eol}{$eol}", (! $asFile ? '        ' : ''), $definition->getDeprecationMessage());
        }

        $code .= $this->addInlineService($definition, null, true, $isProxy);

        if ($isProxy && $definition instanceof ObjectDefinitionContract) {
            $code = $this->proxyDumper->getProxyFactoryCode($definition, $code);
        }

        if (! $asFile) {
            $code = $doc . "     */{$eol}" . $this->generateMethod($methodName, $code, $returnType);
        }

        $this->definitionVariables = $this->inlinedDefinitions = null;
        $this->referenceVariables = $this->serviceCalls = null;
        $this->arraySpaceCount = $arraySpaceCount;

        return [$file, $code];
    }

    /**
     * @param \Viserio\Contract\Container\Definition\Definition $definition
     * @param bool                                              $isProxy
     *
     *@throws \Viserio\Contract\Container\Exception\NotFoundException
     * @throws \Viserio\Contract\Container\Exception\CircularDependencyException
     * @throws \ReflectionException
     *
     * @return string
     */
    private function addServiceInclude(DefinitionContract $definition, bool $isProxy): string
    {
        $code = '';
        $eol = "\n";

        if ($this->inlineRequires && (! $this->isPreload($definition) || $isProxy)) {
            $lineage = [];

            /** @var \Viserio\Contract\Container\Definition\DeprecatedDefinition|\Viserio\Contract\Container\Definition\FactoryDefinition|\Viserio\Contract\Container\Definition\ObjectDefinition $inlinedDefinition */
            foreach ($this->inlinedDefinitions as $inlinedDefinition) {
                if (($inlinedDefinition instanceof ObjectDefinitionContract || $inlinedDefinition instanceof FactoryDefinitionContract) && ! $inlinedDefinition->isDeprecated()) {
                    $this->collectLineage($this->getCorrectLineageClass($inlinedDefinition), $lineage);
                }
            }

            foreach ($this->serviceCalls as $id => [, $behavior]) {
                if (ContainerInterface::class !== $id
                    && $behavior !== 2 /* ReferenceDefinitionContract::IGNORE_ON_UNINITIALIZED_REFERENCE */
                    && $id !== $definition->getName()
                    && $this->containerBuilder->has($id)
                    && $this->isTrivialInstance($serviceCallsDefinition = $this->containerBuilder->findDefinition($id))
                    && ($serviceCallsDefinition instanceof ObjectDefinitionContract || $serviceCallsDefinition instanceof FactoryDefinitionContract)
                ) {
                    $this->collectLineage($this->getCorrectLineageClass($serviceCallsDefinition), $lineage);
                }
            }

            $space = '';
            $asFiles = $this->asFiles;

            if ($asFiles && $isProxy) {
                $space = '                ';
            } elseif (! $asFiles || ($asFiles && $this->inlineFactories) || ($asFiles && ! $definition->isShared())) {
                $space = '        ';
            }

            foreach (\array_diff_key(\array_flip($lineage), $this->inlinedRequires) as $file => $class) {
                $code .= \sprintf("%sinclude_once %s;{$eol}", $space, $file);
            }
        }

        if ('' !== $code) {
            $code .= $eol;
        }

        return $code;
    }

    /**
     * Add a inline service to the code.
     *
     * @param \Viserio\Contract\Container\Definition\Definition                                                                  $definition
     * @param null|\Viserio\Component\Container\Definition\ReferenceDefinition|\Viserio\Contract\Container\Definition\Definition $inlineDef
     * @param bool                                                                                                               $forConstructor
     * @param bool                                                                                                               $isProxy
     *
     *@throws \Viserio\Contract\Container\Exception\NotFoundException
     *
     * @return string
     */
    private function addInlineService(
        DefinitionContract $definition,
        $inlineDef = null,
        bool $forConstructor = true,
        bool $isProxy = false
    ): string {
        $id = $definition->getName();
        $code = '';

        if ($isSimpleInstance = $isRootInstance = $inlineDef === null) {
            foreach ($this->serviceCalls as $targetId => [$callCount, $behavior, $byConstructor]) {
                if ($byConstructor && \array_key_exists($id, $this->circularReferences) && \array_key_exists($targetId, $this->circularReferences[$id]) && ! $this->circularReferences[$id][$targetId]) {
                    $code .= $this->addInlineReference($definition, $targetId, $forConstructor, $isProxy);
                }
            }
        }

        if (isset($this->definitionVariables[$inlineDef = $inlineDef ?: $definition])) {
            return $code;
        }

        $arguments = [];

        if ($inlineDef instanceof ArgumentAwareDefinitionContract && ! $inlineDef instanceof FactoryDefinitionContract) {
            $arguments = [$inlineDef->getArguments()];
        } elseif ($inlineDef instanceof FactoryDefinitionContract) {
            $arguments = [$inlineDef->getClassArguments(), $inlineDef->getArguments()];
        }

        $code .= $this->addInlineVariables($definition, $arguments, $forConstructor, $isProxy);

        if (($inlineDef instanceof ObjectDefinitionContract || $inlineDef instanceof FactoryDefinitionContract) && \count($arguments = \array_filter([$inlineDef->getProperties(), $inlineDef->getMethodCalls(), $inlineDef->getConditions()])) !== 0) {
            $isSimpleInstance = false;
        } elseif ($definition !== $inlineDef && 2 > $this->inlinedDefinitions[$inlineDef]) {
            return $code;
        }

        $eol = "\n";
        $asFile = $this->asFiles && ! $this->inlineFactories;

        if (isset($this->definitionVariables[$inlineDef])) {
            $isSimpleInstance = false;
        } else {
            $name = $definition === $inlineDef ? 'instance' : $this->getNextVariableName();
            $this->definitionVariables[$inlineDef] = new Variable($name);
            $code .= $code !== '' ? $eol : '';

            if ($name === 'instance') {
                $code .= $this->addServiceInstance($definition, $isSimpleInstance, $isProxy) . (! $isSimpleInstance ? $eol : '');

                $forConstructor = false;
            } else {
                $space = '        ';

                if (! $this->asFiles && $isProxy) {
                    $space = '                ';
                } elseif ($asFile) {
                    $space = '';
                }

                $code .= \sprintf("%s$%s = %s;{$eol}", $space, $name, $this->compileValue($inlineDef));

                $forConstructor = $forConstructor && $this->proxyDumper !== null && $definition->isLazy();
            }

            if ('' !== $inline = $this->addInlineVariables($definition, $arguments, $forConstructor, $isProxy)) {
                $code .= $eol . $inline . $eol;
            } elseif (! $isSimpleInstance && $name === 'instance' && \count($arguments) !== 0) {
                $code .= $eol;
            }

            if ($inlineDef instanceof PropertiesAwareDefinitionContract && $inlineDef->getChange('properties')) {
                $code .= $this->addServiceProperties($inlineDef, $name, $isProxy);
            }

            $sharedNonLazyId = null;

            if (! $inlineDef instanceof ReferenceDefinitionContract && $inlineDef instanceof MethodCallsAwareDefinitionContract && $inlineDef->getChange('method_calls')) {
                $code .= $this->addServiceMethodCalls(
                    $inlineDef,
                    $name,
                    $sharedNonLazyId = (! $isProxy && $inlineDef->isShared() && ! isset($this->singleUsePrivateIds[$name]) ? $inlineDef->getName() : null),
                    $isProxy
                );
            }

            if ($inlineDef instanceof DefinitionContract && $inlineDef->getChange('condition')) {
                $code .= $this->addDefinitionCondition($inlineDef, $name, $isProxy, $sharedNonLazyId);
            }
        }

        if ($isRootInstance && ! $isSimpleInstance) {
            if ($this->proxyDumper !== null && $this->proxyDumper->isSupported($definition)) {
                return $code . $eol . ($asFile && ! $this->isPreload($definition) ? '' : '                ') . "\$wrappedInstance = \$instance;{$eol}";
            }

            return $code . $eol . ($asFile && ! $this->isPreload($definition) ? '' : '        ') . 'return $instance;';
        }

        return ($asFile && $isRootInstance && $isSimpleInstance && ! $definition->isShared() ? '        ' : '') . $code;
    }

    /**
     * Add a new service instance to the code.
     *
     * @param \Viserio\Contract\Container\Definition\Definition $definition
     * @param bool                                              $isSimpleInstance
     * @param bool                                              $isProxy
     *
     *@throws \Viserio\Contract\Container\Exception\NotFoundException
     * @throws \Viserio\Contract\Container\Exception\InvalidArgumentException
     * @throws \Viserio\Contract\Container\Exception\RuntimeException
     *
     * @return string
     */
    private function addServiceInstance(
        DefinitionContract $definition,
        bool $isSimpleInstance,
        bool $isProxy = false
    ): string {
        $id = $definition->getName();
        $isProxyCandidate = $this->proxyDumper !== null && $this->proxyDumper->isSupported($definition);
        $instantiation = '';

        $lastWitherIndex = null;

        if ($definition instanceof ObjectDefinitionContract) {
            foreach ($definition->getMethodCalls() as $k => $call) {
                if ($call[2] ?? false) {
                    $lastWitherIndex = $k;
                }
            }
        }

        if (! $isProxyCandidate && $lastWitherIndex === null && ! isset($this->singleUsePrivateIds[$id]) && $definition->isShared()) {
            $instantiation = \sprintf(
                '$this->%s[%s] = %s',
                $this->containerBuilder->getDefinition($id)->isPublic() ? 'services' : 'privates',
                $this->compileValue($id),
                $isSimpleInstance ? '' : '$instance'
            );
        } elseif (! $isSimpleInstance) {
            $instantiation = '$instance';
        }

        $isFile = $this->asFiles && ! $this->inlineFactories && ! $this->isPreload($definition);

        $return = '';

        if ($isProxy) {
            $return = '                ';
        } elseif (! $isFile) {
            $return = '        ';
        }

        if ($isSimpleInstance && ! $isProxy) {
            $return = \sprintf('%sreturn ', $return);
        } elseif ($isSimpleInstance && $isProxy) {
            $return = \sprintf('%s$wrappedInstance = ', $isFile ? '        ' : '                ');
        } else {
            $instantiation .= ' = ';
        }

        return $return . $instantiation . $this->compileValue($definition) . ';';
    }

    /**
     * @param object|string $class
     * @param array         $lineage
     *
     * @throws \ReflectionException
     *
     * @return void
     */
    private function collectLineage($class, array &$lineage): void
    {
        if ($this->containerBuilder instanceof $class || isset($lineage[$class])) {
            return;
        }

        if (! $reflection = $this->containerBuilder->getClassReflector($class, false)) {
            return;
        }

        $file = $reflection->getFileName();

        if (! $file || \var_export($file, true) === $exportedFile = $this->export($file)) {
            return;
        }

        if ($parent = $reflection->getParentClass()) {
            $this->collectLineage($parent->name, $lineage);
        }

        foreach ($reflection->getInterfaces() as $interface) {
            $this->collectLineage($interface->name, $lineage);
        }

        foreach ($reflection->getTraits() as $trait) {
            $this->collectLineage($trait->name, $lineage);
        }

        $lineage[$class] = \substr($exportedFile, 1, -1);
    }

    /**
     * Add removed ids to the container.
     *
     * @return string
     */
    private function addRemovedIds(): string
    {
        $ids = $this->getPreparedRemovedIds();

        if (\count($ids) === 0) {
            return '';
        }

        $eol = "\n";

        if ($this->asFiles) {
            $code = "require \$this->containerDir.'/removed-ids.php'";
        } else {
            $code = "[{$eol}";

            foreach ($ids as $id) {
                if (\preg_match('/^\.\d+_[^~]++~[._a-zA-Z\d]{7}$/', $id)) {
                    continue;
                }

                $code .= \sprintf("            %s => true,{$eol}", $this->compileValue($id));
            }

            $code .= '        ]';
        }

        return "{$eol}{$eol}    /**{$eol}     * {@inheritdoc}{$eol}     */{$eol}" . $this->generateMethod(
            'getRemovedIds',
            \sprintf('        return %s;', $code),
            'array',
            [],
            true
        );
    }

    /**
     * @param string $directory
     *
     * @return void
     */
    private static function createCompilationDirectory(string $directory): void
    {
        if (! \is_dir($directory) && ! @\mkdir($directory, 0777, true)) {
            throw new InvalidArgumentException(\sprintf('Compilation directory does not exist and cannot be created: %s.', $directory));
        }

        if (! \is_writable($directory)) {
            throw new InvalidArgumentException(\sprintf('Compilation directory is not writable: %s.', $directory));
        }
    }

    /**
     * Generate a container method.
     *
     * @param string      $uniqueMethodName
     * @param string      $content
     * @param null|string $return
     * @param array       $parameters
     * @param bool        $public
     * @param bool        $static
     *
     * @return string
     */
    private function generateMethod(
        string $uniqueMethodName,
        string $content,
        ?string $return = null,
        array $parameters = [],
        bool $public = false,
        bool $static = false
    ): string {
        $transformedParameters = [];

        foreach ($parameters as $parameter => $type) {
            $transformedParameters[] = \sprintf('%s $%s', $type, $parameter);
        }

        $eol = "\n";

        return \sprintf(
            '%s%s%s function %s(%s)%s%s{%s}',
            '    ',
            $public ? 'public' : 'protected',
            $static ? ' static' : '',
            $uniqueMethodName,
            \count($parameters) === 0 ? '' : \implode(', ', $transformedParameters),
            $return !== null ? ': ' . $return : '',
            "{$eol}    ",
            "{$eol}{$content}{$eol}    "
        );
    }

    /**
     * Returns the start of the class.
     *
     * @param string      $class
     * @param string      $parentClass
     * @param null|string $namespace
     *
     * @throws \ReflectionException
     *
     * @return string
     */
    private function getClassStartForCompiledClass(string $class, string $parentClass, ?string $namespace): string
    {
        $eol = "\n";

        $code = "<?php{$eol}{$eol}declare(strict_types=1);{$eol}";
        $code .= ($namespace !== null && $this->asFiles === false ? "{$eol}namespace {$namespace};{$eol}" : '') . $eol;
        $code .= "/**{$eol} * This class has been auto-generated by Viserio Container Component.{$eol} */{$eol}";
        $code .= "final class {$class} extends \\{$parentClass}{$eol}{";

        if ($this->asFiles || $this->targetDirRegex) {
            $doc = ($this->asFiles ? "     *{$eol}     * @param array  \$buildParameters{$eol}     * @param string \$containerDir{$eol}" : '');

            $code .= $this->asFiles ? "{$eol}    /**{$eol}     * Path to the container dir.{$eol}     *{$eol}     * @var string \$containerDir{$eol}     */{$eol}    private \$containerDir;{$eol}" : '';
            $code .= "{$eol}    /**{$eol}     * Create a new Compiled Container instance.{$eol}{$doc}     */{$eol}";
        } else {
            $code .= "{$eol}    /**{$eol}     * Create a new Compiled Container instance.{$eol}     */{$eol}";
        }

        $code .= \sprintf("    public function __construct(%s){$eol}    {{$eol}        \$this->services = \$this->privates = [];", $this->asFiles ? 'array $buildParameters = [], string $containerDir = __DIR__' : '');

        if ((new ReflectionClass($parentClass))->getConstructor() !== null) {
            $code .= "{$eol}        parent::__construct();{$eol}";
        }

        if ($this->asFiles) {
            $code .= "{$eol}        \$this->containerDir = \$containerDir;{$eol}";

            if ($this->targetDirRegex !== null) {
                $code = \str_replace('$parameters', "\$targetDir;\n    private \$parameters", $code);
                $code .= "        \$this->targetDir = \\dirname(\$containerDir);{$eol}";
            }
        }

        $code .= $this->addParameters();
        $code .= $this->addMethodMap();
        $code .= $this->asFiles && ! $this->inlineFactories ? $this->addFileMap() : '';
        $code .= $this->addUninitializedServices();
        $code .= $this->addAliases();
        $code .= $this->addSyntheticIds();
        $code .= $this->addInlineRequires();

        return $code . "{$eol}    }";
    }

    /**
     * @return string
     */
    private function addMethodMap(): string
    {
        $definitions = $this->containerBuilder->getDefinitions();

        if (\count($definitions) === 0) {
            return '';
        }

        \ksort($definitions);

        $eol = "\n";
        $methods = [];

        foreach ($definitions as $definition) {
            if (! $definition->isSynthetic() && $definition->isPublic() && (! $this->asFiles || $this->inlineFactories || ! $definition->isShared() || $this->isPreload($definition))) {
                $methods[] = \sprintf("            %s => '%s',{$eol}", $this->compileValue($definition->getName()), 'get' . $definition->getHash());
            }
        }

        $aliases = $this->containerBuilder->getAliases();

        \ksort($aliases);

        foreach ($aliases as $alias => $definition) {
            if ($definition->isDeprecated()) {
                $methods[] = \sprintf("            %s => '%s',{$eol}", $this->compileValue($alias), 'get' . $definition->getHash());
            }
        }

        return \sprintf("{$eol}        \$this->methodMapping = [{$eol}%s        ];", \implode('', $methods));
    }

    /**
     * @return string
     */
    private function addFileMap(): string
    {
        $definitions = $this->containerBuilder->getDefinitions();

        if (\count($definitions) === 0) {
            return '';
        }

        \ksort($definitions);

        $files = [];
        $eol = "\n";

        foreach ($definitions as $id => $definition) {
            if (! $definition->isSynthetic() && $definition->isPublic() && $definition->isShared() && ! $this->isPreload($definition)) {
                $files[] = \sprintf("            %s => 'get%s.php',{$eol}", $this->compileValue($id), $definition->getHash());
            }
        }

        return \sprintf("{$eol}        \$this->fileMap = [{$eol}%s        ];", \implode('', $files));
    }

    /**
     * Create the alias property for the container.
     *
     * @return string
     */
    private function addAliases(): string
    {
        $aliases = $this->containerBuilder->getAliases();

        if (\count($aliases) === 0) {
            return '';
        }

        \ksort($aliases);

        $eol = "\n";
        $code = [];

        foreach ($aliases as $alias => $aliasDefinition) {
            if ($aliasDefinition->isDeprecated()) {
                continue;
            }

            $code[] = \sprintf("            %s => %s,{$eol}", $this->compileValue($alias), $this->compileValue($aliasDefinition->getName()));
        }

        return \sprintf("{$eol}        \$this->aliases = [{$eol}%s        ];", \implode('', $code));
    }

    /**
     * Create the uninitialized services list for the container.
     *
     * @return string
     */
    private function addUninitializedServices(): string
    {
        $uninitialized = $this->uninitializedServices;

        if (\count($uninitialized) === 0) {
            return '';
        }

        \ksort($uninitialized);

        $eol = "\n";
        $code = [];

        foreach ($uninitialized as $id => $bool) {
            $code[] = \sprintf("            %s => true,{$eol}", $this->compileValue($id));
        }

        return \sprintf("{$eol}        \$this->uninitializedServices = [{$eol}%s        ];", \implode('', $code));
    }

    /**
     * Adds synthetic ids to the compiled container.
     *
     * @return string
     */
    private function addSyntheticIds(): string
    {
        $definitions = $this->containerBuilder->getDefinitions();

        if (\count($definitions) === 0) {
            return '';
        }

        \ksort($definitions);

        $code = [];

        foreach ($definitions as $id => $definition) {
            if ($id !== ContainerInterface::class && $definition->isSynthetic()) {
                $code[] = \sprintf('            %s => true,', $this->compileValue($id));
            }
        }

        if (\count($code) === 0) {
            return '';
        }

        $eol = "\n";

        return \sprintf("{$eol}        \$this->syntheticIds = [{$eol}%s{$eol}        ];", \implode('' . $eol, $code));
    }

    /**
     * Add.
     *
     * @throws \ReflectionException
     *
     * @return string
     */
    private function addInlineRequires(): string
    {
        if (! $this->preloadTag || ! $this->inlineRequires) {
            return '';
        }

        $lineage = [];

        foreach ($this->containerBuilder->getTagged($this->preloadTag) as $definitionAndTags) {
            [$definition,] = $definitionAndTags;

            if ($this->proxyDumper !== null && $this->proxyDumper->isSupported($definition)) {
                continue;
            }

            foreach ($this->getDefinitionsFromArguments([$definition]) as $def) {
                if ($def instanceof ObjectDefinitionContract || $def instanceof FactoryDefinitionContract) {
                    $this->collectLineage($this->getCorrectLineageClass($def), $lineage);
                }
            }
        }

        $eol = "\n";
        $code = '';

        foreach ($lineage as $file) {
            if (! isset($this->inlinedRequires[$file])) {
                $this->inlinedRequires[$file] = true;

                $code .= \sprintf("{$eol}        include_once %s;", $file);
            }
        }

        return $code !== '' ? $eol . $code : '';
    }

    /**
     * Return the end of the class.
     *
     * @param null|string $namespace
     *
     * @return string
     */
    private function getClassEndForCompiledClass(?string $namespace): string
    {
        $eol = "\n";

        if ($this->asFiles && ! $this->inlineFactories) {
            $code = "{$eol}{$eol}    /**{$eol}      * {@inheritdoc}{$eol}     */{$eol}    protected function load(string \$file): object{$eol}    {{$eol}        return require \$this->containerDir.'/'.\$file;{$eol}    }";
        } else {
            $code = '';
        }

        if ($this->proxyDumper !== null) {
            $hasProxies = false;

            foreach ($this->containerBuilder->getDefinitions() as $definition) {
                if (! $this->proxyDumper->isSupported($definition)) {
                    continue;
                }

                $hasProxies = true;
            }

            if ($hasProxies) {
                if ($this->asFiles && ! $this->inlineFactories) {
                    $proxyLoader = '$this->load("{$class}.php")';
                } elseif ($namespace !== null || $this->inlineFactories) {
                    $proxyLoader = \sprintf('\\class_alias("%s\\\\{$class}", $class, false)', \addslashes($namespace ?? __NAMESPACE__));
                } else {
                    $proxyLoader = '';
                }

                if ($proxyLoader) {
                    $proxyLoader = "\\class_exists(\$class, false) || {$proxyLoader};{$eol}{$eol}        ";
                }

                // Adds proxy helper function to the compiled container.
                $code .= "{$eol}{$eol}    /**{$eol}     * Invoke a proxy instance.{$eol}     *{$eol}     * @param string   \$class{$eol}     * @param \\Closure \$factory{$eol}     *{$eol}     * @return object{$eol}     */{$eol}    protected function createProxy(string \$class, \\Closure \$factory): object{$eol}    {{$eol}        {$proxyLoader}return \$factory();{$eol}    }";
            }
        }

        return "{$code}{$eol}}{$eol}";
    }

    /**
     * @param \Viserio\Contract\Container\Definition\Definition $definition
     * @param array                                             $arguments
     * @param bool                                              $forConstructor
     * @param bool                                              $isProxy
     *
     *@throws \Viserio\Contract\Container\Exception\NotFoundException
     *
     * @return string
     */
    private function addInlineVariables(
        DefinitionContract $definition,
        array $arguments,
        bool $forConstructor,
        bool $isProxy
    ): string {
        $code = '';
        $this->wrapConditionCalled = false;

        foreach ($arguments as $argument) {
            if (\is_array($argument) && \count($argument) !== 0) {
                $code .= $this->addInlineVariables($definition, $argument, $forConstructor, $isProxy);
            } elseif ($argument instanceof ReferenceDefinitionContract) {
                $code .= $this->addInlineReference($definition, $argument->getName(), $forConstructor, $isProxy);
            } elseif ($argument instanceof DefinitionContract) {
                $code .= $this->addInlineService($definition, $argument, $forConstructor, $isProxy);
            }
        }

        return $code;
    }

    /**
     * Add inline reference code.
     *
     * @param \Viserio\Contract\Container\Definition\Definition $definition
     * @param string                                            $targetId
     * @param bool                                              $forConstructor
     * @param bool                                              $isProxy
     *
     *@throws \Viserio\Contract\Container\Exception\NotFoundException
     *
     * @return string
     */
    private function addInlineReference(
        DefinitionContract $definition,
        string $targetId,
        bool $forConstructor,
        bool $isProxy
    ): string {
        $id = $definition->getName();

        while ($this->containerBuilder->hasAlias($targetId)) {
            $targetId = $this->containerBuilder->getAlias($targetId)->getName();
        }

        [$callCount, $behavior] = $this->serviceCalls[$targetId];

        if ($id === $targetId) {
            return $this->addInlineService($definition, $definition, $forConstructor, $isProxy);
        }

        if (ContainerInterface::class === $targetId || isset($this->referenceVariables[$targetId])) {
            return '';
        }

        $hasSelfRef = isset($this->circularReferences[$id][$targetId]) && ! isset($this->definitionVariables[$definition]);

        if ($hasSelfRef && ! $forConstructor && ! $forConstructor = ! $this->circularReferences[$id][$targetId]) {
            $code = $this->addInlineService($definition, $definition, $forConstructor, $isProxy);
        } else {
            $code = '';
        }

        if (isset($this->referenceVariables[$targetId]) || (2 > $callCount && (! $hasSelfRef || ! $forConstructor))) {
            return $code;
        }

        $name = $this->getNextVariableName();
        $this->referenceVariables[$targetId] = new Variable($name);
        $eol = "\n";

        $isFile = $this->asFiles && ! $this->inlineFactories && ! $this->isPreload($definition);
        $proxySpace = $isProxy ? '                ' : '        ';

        $code .= \sprintf(
            "%s$%s = %s;{$eol}",
            $isFile && ! $isProxy ? '' : $proxySpace,
            $name,
            $this->compileReferenceDefinition(new ReferenceDefinition($targetId, $behavior))
        );

        if (! $hasSelfRef || ! $forConstructor) {
            return $code;
        }

        $ifSpace = $isFile && ! $isProxy ? '        ' : $proxySpace;

        return $code . \sprintf(
            "{$eol}{$ifSpace}if (isset(\$this->%s[%s])) {{$eol}{$ifSpace}    return \$this->%1\$s[%2\$s];{$eol}{$ifSpace}}{$eol}",
            $this->containerBuilder->getDefinition($id)->isPublic() ? 'services' : 'privates',
            $this->compileValue($id)
        );
    }

    /**
     * @param string                                                  $sourceId
     * @param \Viserio\Contract\Container\ServiceReferenceGraphEdge[] $edges
     * @param array                                                   $checkedNodes
     * @param array                                                   $currentPath
     * @param bool                                                    $byConstructor
     */
    private function analyzeCircularReferences(
        string $sourceId,
        array $edges,
        array &$checkedNodes,
        array &$currentPath = [],
        bool $byConstructor = true
    ): void {
        $checkedNodes[$sourceId] = true;
        $currentPath[$sourceId] = $byConstructor;

        foreach ($edges as $edge) {
            $node = $edge->getDestNode();
            $id = $node->getId();

            if ($sourceId === $id || ! $node->getValue() instanceof DefinitionContract || $edge->isLazy() || $edge->isWeak()) {
                // no-op
            } elseif (isset($currentPath[$id])) {
                $this->addCircularReferences($id, $currentPath, $edge->isReferencedByConstructor());
            } elseif (! isset($checkedNodes[$id])) {
                $this->analyzeCircularReferences($id, $node->getOutEdges(), $checkedNodes, $currentPath, $edge->isReferencedByConstructor());
            } elseif (isset($this->circularReferences[$id])) {
                $this->connectCircularReferences($id, $currentPath, $edge->isReferencedByConstructor());
            }
        }

        unset($currentPath[$sourceId]);
    }

    /**
     * @param string $sourceId
     * @param array  $currentPath
     * @param mixed  $byConstructor
     * @param array  $subPath
     */
    private function connectCircularReferences(
        string $sourceId,
        array &$currentPath,
        $byConstructor,
        array &$subPath = []
    ): void {
        $currentPath[$sourceId] = $subPath[$sourceId] = $byConstructor;

        foreach ($this->circularReferences[$sourceId] as $id => $byRefConstructor) {
            if (isset($currentPath[$id])) {
                $this->addCircularReferences($id, $currentPath, $byRefConstructor);
            } elseif (! isset($subPath[$id]) && isset($this->circularReferences[$id])) {
                $this->connectCircularReferences($id, $currentPath, $byRefConstructor, $subPath);
            }
        }

        unset($currentPath[$sourceId], $subPath[$sourceId]);
    }

    /**
     * @param string $id
     * @param array  $currentPath
     * @param bool   $byConstructor
     *
     * @return void
     */
    private function addCircularReferences(string $id, array $currentPath, bool $byConstructor): void
    {
        $currentPath[$id] = $byConstructor;

        $circularRefs = [];

        foreach (\array_reverse($currentPath) as $parentId => $v) {
            $byConstructor = $byConstructor && (bool) $v;
            $circularRefs[] = $parentId;

            if ($parentId === $id) {
                break;
            }
        }

        $currentId = $id;

        foreach ($circularRefs as $parentId) {
            if (! isset($this->circularReferences[$parentId][$currentId])) {
                $this->circularReferences[$parentId][$currentId] = $byConstructor;
            }

            $currentId = $parentId;
        }
    }

    /**
     * @param array                  $arguments
     * @param null|\SplObjectStorage $definitions
     * @param array                  $calls
     * @param bool                   $byConstructor
     *
     * @return \SplObjectStorage
     */
    private function getDefinitionsFromArguments(
        array $arguments,
        SplObjectStorage $definitions = null,
        array &$calls = [],
        bool $byConstructor = null
    ): SplObjectStorage {
        if ($definitions === null) {
            $definitions = new \SplObjectStorage();
        }

        foreach ($arguments as $argument) {
            if (\is_array($argument)) {
                $this->getDefinitionsFromArguments($argument, $definitions, $calls, $byConstructor);
            } elseif ($argument instanceof ReferenceDefinitionContract) {
                $id = $argument->getName();

                while ($this->containerBuilder->hasAlias($id)) {
                    $id = $this->containerBuilder->getAlias($id)->getName();
                }

                if (! isset($calls[$id])) {
                    $calls[$id] = [0, $argument->getBehavior(), $byConstructor];
                } else {
                    $calls[$id][1] = \min($calls[$id][1], $argument->getBehavior());
                }

                $calls[$id][0]++;
            } elseif (! $argument instanceof DefinitionContract) {
                // no-op
            } elseif (isset($definitions[$argument])) {
                $definitions[$argument] = 1 + $definitions[$argument];
            } else {
                $definitions[$argument] = 1;
                $arguments = [];

                if ($argument instanceof ArgumentAwareDefinitionContract) {
                    $arguments[] = $argument->getArguments();
                }

                if ($argument instanceof FactoryDefinitionContract) {
                    $arguments[] = $argument->getClassArguments();
                }

                $this->getDefinitionsFromArguments($arguments, $definitions, $calls, $byConstructor === null || $byConstructor);

                $arguments = [];

                if ($argument instanceof PropertiesAwareDefinitionContract) {
                    $arguments[] = $argument->getProperties();
                }

                if ($argument instanceof MethodCallsAwareDefinitionContract) {
                    $arguments[] = $argument->getMethodCalls();
                }

                if ($argument instanceof FactoryDefinitionContract) {
                    $arguments[] = $argument->getClassArguments();
                }

                $this->getDefinitionsFromArguments($arguments, $definitions, $calls, $byConstructor !== null && $byConstructor);
            }
        }

        return $definitions;
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    private function export($value): string
    {
        if (\is_int($value) || \is_float($value)) {
            return \var_export($value, true);
        }

        if ($value === false) {
            return 'false';
        }

        if ($value === true) {
            return 'true';
        }

        if ($value === null) {
            return 'null';
        }

        if ($value === '') {
            return "''";
        }

        if ($this->targetDirRegex !== null && \is_string($value) && \preg_match($this->targetDirRegex, $value, $matches, \PREG_OFFSET_CAPTURE)) {
            $value = self::normalizePath($value);

            $prefix = $matches[0][1] ? \var_export(\substr($value, 0, $matches[0][1]), true) . '.' : '';
            $suffix = $matches[0][1] + \strlen($matches[0][0]);
            $suffix = isset($value[$suffix]) ? '.' . \var_export(\substr($value, $suffix), true) : '';
            $dirname = $this->asFiles ? '$this->containerDir' : '__DIR__';
            $offset = 1 + $this->targetDirMaxMatches - \count($matches);

            if (0 < $offset) {
                $dirname = \sprintf('\dirname(__DIR__, %d)', $offset + (int) $this->asFiles);
            } elseif ($this->asFiles) {
                $dirname = "\$this->targetDir.''"; // empty string concatenation on purpose
            }

            if ($prefix || $suffix) {
                return \sprintf('(%s%s%s)', $prefix, $dirname, $suffix);
            }

            return $dirname;
        }

        if (\is_string($value)) {
            $class = \ltrim($value, '\\');

            if ($class === 'stdClass') {
                return '\\stdClass::class';
            }

            if (isset($value[0]) && \strtolower($value[0]) !== $value[0] && (\class_exists($class) || \interface_exists($class))) {
                return \sprintf('%s::class', $this->generateLiteralClass($value));
            }

            $subIndent = '    ';
            $value = \var_export($value, true);

            if (\strpos($value, "\n") !== false || \strpos($value, "\r") !== false) {
                $value = \strtr($value, [
                    "\r\n" => "'.\"\\r\\n\"\n" . $subIndent . ".'",
                    "\r" => "'.\"\\r\"\n" . $subIndent . ".'",
                    "\n" => "'.\"\\n\"\n" . $subIndent . ".'",
                ]);
            }

            if (\strpos($value, "\0") !== false) {
                $value = \str_replace(['\' . "\0" . \'', '".\'\'."'], ['\'."\0".\'', ''], $value);
            }

            if (\strpos($value, "''.") !== false) {
                $value = \str_replace("''.", '', $value);
            }

            if (\substr($value, -3) === ".''") {
                $value = \rtrim(\substr($value, 0, -3));
            }

            return $value;
        }

        return \var_export($value, true);
    }

    /**
     * @param array  $options
     * @param array  $proxyClasses
     * @param string $code
     * @param string $classEndCode
     *
     * @throws \ReflectionException
     *
     * @return array
     */
    private function generateFileContainer(
        array $options,
        array $proxyClasses,
        string $code,
        string $classEndCode
    ): array {
        $eol = "\n";
        $class = $options['class'];
        $files = [];
        $ids = $this->getPreparedRemovedIds();

        if (\count($ids) !== 0) {
            $fileContent = "<?php{$eol}{$eol}return [{$eol}";

            foreach ($ids as $id) {
                if (\preg_match('/^\.\d+_[^~]++~[._a-zA-Z\d]{7}$/', $id)) {
                    continue;
                }

                $fileContent .= '    ' . \sprintf("%s => true,{$eol}", $this->compileValue($id));
            }

            $files['removed-ids.php'] = $fileContent .= "];{$eol}";
        }

        if ($this->asFiles && ! $this->inlineFactories) {
            foreach ($this->generateServiceFiles($this->services) as $file => $fileContent) {
                $files[$file] = "<?php{$eol}{$eol}declare(strict_types=1);{$eol}{$eol}/**{$eol} * This class has been auto-generated by Viserio Container Component for internal use.{$eol} */{$eol}{$fileContent}";
            }

            foreach ($proxyClasses as $file => $fileContent) {
                $files[$file] = "<?php{$eol}" . $fileContent;
            }

            $files[$class . '.php'] = $code . $classEndCode;
        } elseif ($this->inlineFactories) {
            $code .= $classEndCode;

            foreach ($proxyClasses as $fileContent) {
                $code .= $fileContent;
            }

            $files[$class . '.php'] = $code;
        }

        $hash = \ucfirst(\strtr(ContainerBuilder::getHash(\serialize($files)), '._', 'xx'));
        $code = [];

        foreach ($files as $file => $c) {
            $code["Container{$hash}/{$file}"] = $c;
        }

        \array_pop($code);

        $code["Container{$hash}/{$class}.php"] = \substr_replace($files[$class . '.php'], "{$eol}{$eol}namespace Container{$hash};", 31, 0);

        $namespace = $options['namespace'];
        $time = $options['build_time'];
        $hashTime = \hash('crc32', $hash . $time);

        $this->asFiles = false;

        $fileContentCode = "<?php{$eol}{$eol}declare(strict_types=1);{$eol}";
        $fileContentCode .= ($namespace !== null ? "{$eol}namespace {$namespace};{$eol}{$eol}" : $eol);
        $fileContentCode .= "/**{$eol} * This class has been auto-generated by Viserio Container Component.{$eol} */{$eol}";

        $code[$class . '.php'] = <<<EOF
            {$fileContentCode}
            if (\\class_exists(\\Container{$hash}\\{$class}::class, false)) {
                // no-op
            } elseif (!include __DIR__.'/Container{$hash}/{$class}.php') {
                touch(__DIR__.'/Container{$hash}.legacy');
                return;
            }
            
            if (!\\class_exists({$class}::class, false)) {
                \\class_alias(\\Container{$hash}\\{$class}::class, {$class}::class, false);
            }
            
            return new \\Container{$hash}\\{$class}([
                'container.build_hash' => '{$hash}',
                'container.build_id' => '{$hashTime}',
                'container.build_time' => {$time},
            ], __DIR__.'/Container{$hash}');{$eol}
            EOF;

        return $code;
    }

    /**
     * Generate proxy classes.
     *
     * @throws \ReflectionException
     *
     * @return array
     */
    private function generateProxyClasses(): array
    {
        if ($this->proxyDumper === null) {
            return [];
        }

        $proxyClasses = [];
        $alreadyGenerated = [];
        $definitions = $this->containerBuilder->getDefinitions();

        \ksort($definitions);

        foreach ($definitions as $definition) {
            if ($this->proxyDumper !== null && ! $this->proxyDumper->isSupported($definition)) {
                continue;
            }

            /** @var \Viserio\Contract\Container\Definition\ObjectDefinition $definition */
            if (isset($alreadyGenerated[$class = $this->getCorrectLineageClass($definition)])) {
                continue;
            }

            $alreadyGenerated[$class] = true;

            if ("\n" === $proxyCode = "\n" . $this->proxyDumper->getProxyCode($definition)) {
                continue;
            }

            $code = '';

            if ($this->inlineRequires) {
                $lineage = [];

                $this->collectLineage($class, $lineage);

                foreach (\array_diff_key(\array_flip($lineage), $this->inlinedRequires) as $file => $c) {
                    if ($this->inlineFactories) {
                        $this->inlinedRequires[$file] = true;
                    }

                    $code .= \sprintf("include_once %s;\n", $file);
                }

                $code = $code !== '' ? "\n" . $code : $code;
            }

            if (! $this->debug) {
                $proxyCode = "<?php\n" . $proxyCode;
                $proxyCode = \substr(Util::stripComments($proxyCode), 5);
            }

            $proxyClasses[\sprintf('%s.php', \explode(' ', $proxyCode, 3)[1])] = $code . $proxyCode;
        }

        return $proxyClasses;
    }

    /**
     * @param array $services
     *
     * @return null|Generator
     */
    private function generateServiceFiles(array $services): Generator
    {
        $definitions = $this->containerBuilder->getDefinitions();

        \ksort($definitions);
        $eol = "\n";

        foreach ($definitions as $id => $definition) {
            if (\array_key_exists($id, $services)) {
                [$file, $code] = $services[$id];

                if ($file !== null && ($definition->isPublic() || ! $this->isTrivialInstance($definition))) {
                    if (! $definition->isShared() && ! $this->isPreload($definition)) {
                        $i = \strpos($code, "{$eol}{$eol}include_once ");

                        if ($i !== false && false !== $i = \strpos($code, $eol . $eol, 2 + $i)) {
                            $code = [\substr($code, 0, 2 + $i), \substr($code, 2 + $i)];
                        } else {
                            $code = [$eol, $code];
                        }
                    }

                    yield $file => $code;
                }
            }
        }

        return new EmptyIterator();
    }

    /**
     * Compile know values to executable php code for the compiled container.
     *
     * @param mixed $value
     *
     * @return string
     */
    private function compileValue($value): string
    {
        if (\is_array($value)) {
            return $this->compileArray($value);
        }

        if ($value instanceof ArgumentContract) {
            $scope = [$this->definitionVariables, $this->referenceVariables];
            $this->definitionVariables = $this->referenceVariables = null;

            try {
                if ($value instanceof ClosureArgument) {
                    $value = $value->getValue()[0];
                    $returnedType = '';

                    if (null !== $type = $value->getType()) {
                        $returnedType = \sprintf(': %s\%s', 0 /* ReferenceDefinitionContract::RUNTIME_EXCEPTION_ON_INVALID_REFERENCE */ >= $value->getBehavior() ? '' : '?', $type);
                    }

                    $stringCode = $this->compileValue($value);
                    $eol = "\n";

                    return \sprintf("%sfunction ()%s {{$eol}            %s{$eol}        }", \is_int(\strpos($stringCode, '$this')) ? '' : 'static ', $returnedType, \sprintf('return %s;', $stringCode));
                }

                if ($value instanceof IteratorArgument) {
                    return $this->compileIterator($value->getValue());
                }

                if ($value instanceof ArrayArgument) {
                    return $this->compileArray($value->getValue(), true);
                }
            } finally {
                [$this->definitionVariables, $this->referenceVariables] = $scope;
            }
        }

        if ($value instanceof ContainerInterface) {
            return '$this';
        }

        if ($value instanceof Closure) {
            if (! \class_exists(Standard::class) && ! \class_exists(ReflectionClosure::class)) {
                throw new MissingPackageException(['nikic/php-parser'], self::class, ', closure dumping');
            }

            return $this->compileClosure($value);
        }

        if ($value instanceof Variable) {
            return '$' . $value;
        }

        if ($value instanceof ReferenceDefinitionContract) {
            $id = $value->getName();

            while ($this->containerBuilder->hasAlias($id)) {
                $id = $this->containerBuilder->getAlias($id);
            }

            if ($this->referenceVariables !== null && isset($this->referenceVariables[$id])) {
                $stringCode = $this->compileValue($this->referenceVariables[$id]);
            } else {
                $stringCode = $this->compileReferenceDefinition($value);
            }

            $methodCalls = $value->getMethodCalls();

            if (\count($methodCalls) !== 0) {
                $methodCall = $methodCalls[0];
                $parameters = [];

                foreach ($methodCall[1] as $v) {
                    $parameters[] = $this->definitionVariables !== null && \is_object($v) && $this->definitionVariables->contains($v) ? $this->compileValue($this->definitionVariables[$v]) : $this->compileValue($v);
                }

                $stringCode .= \sprintf('->%s(%s)', $methodCall[0], \implode(', ', $parameters));
            }

            return $stringCode;
        }

        if ($value instanceof ArrayDefinition) {
            return $this->compileArray($value->getValue());
        }

        if ($value instanceof ParameterDefinition) {
            return $this->export($value->getValue());
        }

        if ($value instanceof ClosureDefinition) {
            $args = [];

            foreach ($value->getArguments() as $arg) {
                $args[] = $this->definitionVariables !== null && \is_object($arg) && $this->definitionVariables->contains($arg) ? $this->compileValue($this->definitionVariables[$arg]) : $this->compileValue($arg);
            }

            $executable = $value->isExecutable();
            $compiledClosure = $this->compileClosure($value->getValue());

            if ($executable === false && \count($args) !== 0) {
                throw new InvalidArgumentException(\sprintf('[%s] needs to be executable to use closure arguments.', $value->getName()));
            }

            if ($executable === false) {
                return $compiledClosure;
            }

            return \sprintf('(%s)(%s)', $compiledClosure, \implode(', ', $args));
        }

        if ($value instanceof IteratorDefinition) {
            return $this->compileIterator($value->getValue());
        }

        if ($value instanceof FactoryDefinitionContract) {
            return $this->compileFactoryDefinition($value);
        }

        if ($value instanceof ObjectDefinitionContract) {
            $className = $value->getClass();

            if (is_anonymous_class($className)) {
                return $this->compileValue($value->getValue());
            }

            $args = [];

            foreach ($value->getArguments() as $arg) {
                $args[] = $this->definitionVariables !== null && \is_object($arg) && $this->definitionVariables->contains($arg) ? $this->compileValue($this->definitionVariables[$arg]) : $this->compileValue($arg);
            }

            return \sprintf('new %s(%s)', $this->generateLiteralClass($className), \implode(', ', $args));
        }

        if (\is_object($value) && is_anonymous_class($className = \get_class($value))) {
            [$args, $class] = $this->compileAnonymousObject($className);

            return $this->doInsertStringBeforePosition(
                $class,
                \count($args) === 0 ? '()' : '(\'' . \implode('\', \'', $args) . '\')',
                9
            );
        }

        if (\is_object($value)) {
            return \sprintf('new %s()', $this->generateLiteralClass(\get_class($value)));
        }

        return $this->export($value);
    }

    /**
     * Compile class properties.
     *
     * @param \Viserio\Component\Container\Definition\ConditionDefinition|\Viserio\Contract\Container\Definition\FactoryDefinition|\Viserio\Contract\Container\Definition\ObjectDefinition|\Viserio\Contract\Container\Definition\UndefinedDefinition $definition
     * @param string                                                                                                                                                                                                                                  $variableName
     * @param bool                                                                                                                                                                                                                                    $isProxy
     *
     * @return string
     */
    private function addServiceProperties($definition, string $variableName = 'instance', bool $isProxy = false): string
    {
        $compiledPropertyBinding = '';
        $eol = "\n";
        $isFileDefinition = $this->asFiles && ! $this->inlineFactories && ! $this->isPreload($definition);
        $space = '';

        if (! $isFileDefinition) {
            $space = '        ';
        }

        if ($isProxy) {
            $this->arraySpaceCount = 4;
            $space .= '        ';
        }

        foreach ($definition->getProperties() as $name => [$property, $static]) {
            // Regex was taken from https://www.php.net/manual/en/language.variables.basics.php
            $buildProperty = \sprintf(
                '%s = %s',
                \preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $name) === 1 ? $name : '{' . $this->compileValue($name) . '}',
                $this->definitionVariables !== null && \is_object($property) && $this->definitionVariables->contains($property) ? $this->compileValue($this->definitionVariables[$property]) : $this->compileValue($property)
            );

            $compiledPropertyBinding .= \sprintf("%s%s%s%s;{$eol}", $space, $static ? $this->generateLiteralClass($definition->getClass()) : "\${$variableName}", $static ? '::' : '->', $buildProperty);
        }

        $this->arraySpaceCount = 2;

        return $compiledPropertyBinding;
    }

    /**
     * Compile object method calls to php code.
     *
     * @param mixed       $definition
     * @param string      $variableName
     * @param null|string $sharedNonLazyId
     * @param bool        $isProxy
     *
     *@throws \Viserio\Contract\Container\Exception\NotFoundException
     *
     * @return string
     */
    private function addServiceMethodCalls(
        $definition,
        string $variableName,
        ?string $sharedNonLazyId,
        bool $isProxy = false
    ): string {
        $lastWitherIndex = null;
        $methodCalls = $definition->getMethodCalls();

        foreach ($methodCalls as $k => $call) {
            if ($call[2] ?? false) {
                $lastWitherIndex = $k;
            }
        }

        $calls = '';
        $eol = "\n";

        foreach ($methodCalls as $k => $call) {
            $parameters = [];

            foreach ($call[1] as $v) {
                $parameters[] = $this->definitionVariables !== null && \is_object($v) && $this->definitionVariables->contains($v) ? $this->compileValue($this->definitionVariables[$v]) : $this->compileValue($v);
            }

            $witherAssignation = '';

            if ($call[2] ?? false) { // key 2 = return clone
                if ($sharedNonLazyId !== null && $lastWitherIndex === $k) {
                    $witherAssignation = \sprintf('$this->%s[%s] = ', $definition->isPublic() ? 'services' : 'privates', $this->compileValue($sharedNonLazyId));
                }

                $witherAssignation .= \sprintf('$%s = ', $variableName);
            }

            $calls .= $this->wrapServiceConditionals(
                $call[1],
                \sprintf(
                    "%s%s$%s->%s(%s);{$eol}",
                    $this->asFiles && ! $this->inlineFactories && ! $this->isPreload($definition) ? '' : ($isProxy ? '            ' : '        '),
                    $witherAssignation,
                    $variableName,
                    $call[0],
                    \implode(', ', $parameters)
                )
            );
        }

        return $calls;
    }

    /**
     * Dumps a string to a literal (aka PHP Code) class value.
     *
     * @param string $class
     *
     * @return string
     */
    private function generateLiteralClass(string $class): string
    {
        $class = \str_replace('\\\\', '\\', $class);

        return \str_replace('::class', '', \strpos($class, '\\') === 0 ? $class : '\\' . $class);
    }

    /**
     * Insert string before specified position.
     *
     * @param string $string
     * @param string $insertStr
     * @param int    $position
     *
     * @return string
     */
    private function doInsertStringBeforePosition(string $string, string $insertStr, int $position): string
    {
        return \substr($string, 0, $position) . $insertStr . \substr($string, $position);
    }

    /**
     * Analyze a closure.
     *
     * @param \Closure $closure
     *
     * @return string
     */
    private function compileClosure(Closure $closure): string
    {
        if ($this->phpParser === null) {
            throw new RuntimeException(\sprintf('Instance of [%s] was not initialized, you should call the [%s::__construct] with the second variable to allow compiling of closure.', Parser::class, self::class));
        }

        if ($this->printer === null) {
            throw new RuntimeException(\sprintf('Instance of [%s] was not initialized, you should call the [%s::__construct] with the third variable to allow compiling of closure.', Standard::class, self::class));
        }

        $reflection = $this->containerBuilder->getFunctionReflector($closure);
        $fileName = $reflection->getFileName();

        Util::checkFile($fileName);

        $ast = $this->phpParser->parse(\file_get_contents($fileName));

        try {
            $locator = new ClosureLocatorVisitor($reflection);

            $fileTraverser = new NodeTraverser();
            $fileTraverser->addVisitor(new NameResolver());
            $fileTraverser->addVisitor($usesCollectorNodeVisitor = new UsesCollectorNodeVisitor());
            $fileTraverser->addVisitor($locator);
            $fileTraverser->traverse($ast);
        } catch (Error $exception) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('There was an error analyzing the closure code.', 0, $exception);
            // @codeCoverageIgnoreEnd
        }

        $closureNode = $locator->closureNode;

        if (! $closureNode) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('The closure was not found within the abstract syntax tree.');
            // @codeCoverageIgnoreEnd
        }

        $location = $locator->location;

        // Make a second pass through the AST, but only through the closure's
        // nodes, to resolve any magic constants to literal values.
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new MagicConstantVisitor($location));
        $traverser->addVisitor($thisDetector = new ThisDetectorVisitor());
        // The first value is the needed closure.
        /** @var \PhpParser\Node[] $closureAst */
        $closureAst = $traverser->traverse([$closureNode])[0];
        $boundClass = $reflection->getClosureThis();

        /** @var null|array|false $statement */
        foreach ($closureAst as $statement) {
            if (\is_array($statement)) {
                foreach ($statement as $node) {
                    if ($node instanceof Expression && $node->expr instanceof Assign && ($expr = $node->expr->expr) instanceof ClassConstFetch) {
                        /** @var \PhpParser\Node\Name $class */
                        $class = $expr->class;
                        $parts = $class->parts;

                        foreach ($parts as $key => $part) {
                            if ($part === 'self') {
                                $parts[$key] = $this->generateLiteralClass(\get_class($boundClass));
                            }
                        }

                        $class->parts = $parts;
                    }

                    if ($node instanceof New_) {
                        $node = $node->class;

                        if ($node === null) {
                            continue;
                        }
                    } elseif ($node instanceof Param) {
                        $node = $node->type;
                    } elseif ($node instanceof Return_ && $node->expr instanceof New_) {
                        $node = $node->expr->class;
                    } else {
                        continue;
                    }

                    $this->applyNamespaceToClass($node, $usesCollectorNodeVisitor);
                }
            }
        }

        $detected = $thisDetector->detected;

        if ($detected) {
            throw new RuntimeException('[$this] cant be used in compiled closure.');
        }

        $closureNode->static = ! $detected;

        return \trim($this->printer->prettyPrint($closureAst), "\t\n\r;");
    }

    /**
     * Analyze a closure.
     *
     * @param string $className
     *
     * @throws \ReflectionException
     *
     * @return array
     */
    private function compileAnonymousObject(string $className): array
    {
        if ($this->phpParser === null) {
            throw new RuntimeException(\sprintf('Instance of [%s] was not initialized, you should call the [%s::__construct] with the second variable to allow compiling of closure.', Parser::class, self::class));
        }

        if ($this->printer === null) {
            throw new RuntimeException(\sprintf('Instance of [%s] was not initialized, you should call the [%s::__construct] with the third variable to allow compiling of closure.', Standard::class, self::class));
        }

        $reflection = $this->containerBuilder->getClassReflector($className);
        $fileName = $reflection->getFileName();

        Util::checkFile($fileName);

        $ast = $this->phpParser->parse(\file_get_contents($fileName));

        $locator = new AnonymousClassLocatorVisitor($reflection);

        $fileTraverser = new NodeTraverser();
        $fileTraverser->addVisitor(new NameResolver());
        $fileTraverser->addVisitor($usesCollectorNodeVisitor = new UsesCollectorNodeVisitor());
        $fileTraverser->addVisitor($locator);
        $fileTraverser->traverse($ast);

        $anonymousClassNode = $locator->anonymousClassNode;

        if (! $anonymousClassNode) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('The anonymous class was not found within the abstract syntax tree.');
            // @codeCoverageIgnoreEnd
        }

        $location = $locator->location;

        // Make a second pass through the AST, but only through the closure's
        // nodes, to resolve any magic constants to literal values.
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new MagicConstantVisitor($location));
        // The first value is the needed closure.
        /** @var \PhpParser\Node\Expr\New_ $anonymousClassAst */
        $anonymousClassAst = $traverser->traverse([$anonymousClassNode])[0];

        /** @var \PhpParser\Node\Stmt\ClassLike $class */
        $class = $anonymousClassAst->class;
        /** @var mixed $node */
        foreach ($class->stmts as $node) {
            if ($node instanceof New_) {
                $node = $node->class;

                if ($node === null) {
                    continue;
                }
            } elseif ($node instanceof Param) {
                $node = $node->type;
            } elseif ($node instanceof TraitUse || $node instanceof ClassMethod) {
                // dont skip traits
            } else {
                continue;
            }

            $this->applyNamespaceToClass($node, $usesCollectorNodeVisitor);
        }

        $args = [];

        if ($anonymousClassAst->args !== null) {
            foreach ($anonymousClassAst->args as $arg) {
                /** @var \PhpParser\Node\Scalar\String_ $value */
                $value = $arg->value;

                $args[] = $value->value;
            }

            $anonymousClassAst->args = null;
        }

        return [
            $args,
            $this->printer->prettyPrint([$anonymousClassAst]),
        ];
    }

    /**
     * Applies missing namespaces to used classes.
     *
     * @param \PhpParser\Node\Expr\New_|\PhpParser\Node\Name|\PhpParser\Node\Stmt|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\TraitUse $node
     * @param \Viserio\Component\Container\PhpParser\NodeVisitor\UsesCollectorNodeVisitor                                                        $usesCollectorNodeVisitor
     *
     * @return void
     */
    private function applyNamespaceToClass($node, UsesCollectorNodeVisitor $usesCollectorNodeVisitor): void
    {
        if ($node instanceof TraitUse) {
            foreach ($node->traits as $trait) {
                $this->applyNamespaceToClass($trait, $usesCollectorNodeVisitor);
            }

            return;
        }

        if ($node instanceof ClassMethod) {
            foreach ($node->params as $param) {
                $this->applyNamespaceToClass($param->type, $usesCollectorNodeVisitor);
            }

            return;
        }

        if ($node instanceof Name) {
            $className = \implode('\\', $node->parts);

            $node->parts = '\\' . $className;

            foreach ($usesCollectorNodeVisitor->getUses() as $use => $alias) {
                $globalNamespaceClass = '\\' . $use . '\\' . $className;

                if (\strpos($use, $className) !== false || ($alias !== null && \strpos($alias, $className) !== false)) {
                    $node->parts = [$use];
                } elseif (\class_exists($globalNamespaceClass) || \interface_exists($globalNamespaceClass)) {
                    $node->parts = [$globalNamespaceClass];
                }
            }
        }
    }

    /**
     * Compile Iterator to a php string code.
     *
     * @param array $values
     *
     *@throws \Viserio\Contract\Container\Exception\NotFoundException
     *
     * @return string
     */
    private function compileIterator($values): string
    {
        $operands = [0];
        $code = [];
        $code[] = 'new \Viserio\Component\Container\RewindableGenerator(function () {';
        $thisIsUsed = [false];
        $eol = "\n";
        $countCode = [];

        if (\count($values) === 0) {
            $thisIsUsed = [false];
            $code[] = '            return new \\EmptyIterator();';
        } else {
            $countCode[] = 'function () {';

            foreach ($values as $k => $v) {
                ($c = $this->getServiceConditionals($v)) ? $operands[] = "(int) ({$c})" : ++$operands[0];
                $v = $this->wrapServiceConditionals($v, \sprintf('        yield %s => %s;', $this->compileValue($k), $this->compileValue($v)), true);

                foreach (\explode($eol, $v) as $value) {
                    if ($value) {
                        $thisIsUsed[] = \is_int(\strpos($value, '$this'));
                        $code[] = '    ' . $value;
                    }
                }
            }

            $countCode[] = \sprintf('            return %s;', \implode(' + ', $operands));
            $countCode[] = \sprintf('%s}', $this->asFiles && ! $this->inlineFactories ? '' : '        ');
        }

        $code[] = \sprintf('        }, %s)', \count($operands) > 1 ? \implode($eol, $countCode) : $operands[0]);

        return \implode($eol, \in_array(true, $thisIsUsed, true) ? $code : \str_replace('RewindableGenerator(f', 'RewindableGenerator(static f', $code));
    }

    /**
     * Compile Factroy Definition to a php string code.
     *
     * @param \Viserio\Contract\Container\Definition\FactoryDefinition $value
     *
     * @return string
     */
    private function compileFactoryDefinition(FactoryDefinitionContract $value): string
    {
        $callable = $value->getValue();
        $compiledArguments = [];

        foreach ($value->getArguments() as $arg) {
            $compiledArguments[] = $this->definitionVariables !== null && \is_object($arg) && $this->definitionVariables->contains($arg) ? $this->compileValue($this->definitionVariables[$arg]) : $this->compileValue($arg);
        }

        if ($callable[0] instanceof ReferenceDefinitionContract || $callable[0] instanceof ObjectDefinitionContract || $callable[0] instanceof FactoryDefinitionContract) {
            return \sprintf(
                '%s->%s(%s)',
                $this->compileValue(
                    $this->definitionVariables->contains($callable[0]) ? $this->definitionVariables[$callable[0]] : $callable[0]
                ),
                $callable[1],
                \implode(', ', $compiledArguments)
            );
        }

        $class = $callable[0];

        if (\is_object($class)) {
            $class = $this->compileValue($callable[0]);
        }

        // If the class is a string we can optimize away
        if ($value->isStatic() || (\strpos($class, "'") === 0 && \strpos($class, '$') === false)) {
            if ($class === "''") {
                throw new CompileException(\sprintf('Cannot dump definition: [%s] service is defined to be created by a factory but is missing the service reference, did you forget to define the factory service id or class?', $value->getName() ? 'The "' . $value->getName() . '"' : 'inline'));
            }

            return \sprintf('%s::%s(%s)', $this->generateLiteralClass($class), $callable[1], \implode(', ', $compiledArguments));
        }

        $compiledClassArguments = [];

        foreach ($value->getClassArguments() as $arg) {
            $compiledClassArguments[] = $this->definitionVariables !== null && \is_object($arg) && $this->definitionVariables->contains($arg) ? $this->compileValue($this->definitionVariables[$arg]) : $this->compileValue($arg);
        }

        if ($callable[1] === '__invoke') {
            return \sprintf('(%s%s(%s))->__invoke(%s)', \strpos($class, 'new ') === 0 ? '' : 'new ', $this->generateLiteralClass($class), \implode(', ', $compiledClassArguments), \implode(', ', $compiledArguments));
        }

        if (\strpos($class, 'new ') === 0) {
            return \sprintf('(%s(%s))->%s(%s)', \str_replace('()', '', $class), \implode(', ', $compiledClassArguments), $callable[1], \implode(', ', $compiledArguments));
        }

        if (! $value->isStatic()) {
            return \sprintf('(new %s(%s))->%s(%s)', $this->generateLiteralClass($class), \implode(', ', $compiledClassArguments), $callable[1], \implode(', ', $compiledArguments));
        }

        return \sprintf('([%s, \'%s\'])(%s)', $this->generateLiteralClass($class), $callable[1], \implode(', ', $compiledArguments));
    }

    /**
     * Compile Reference Definition to a php string code.
     *
     * @param \Viserio\Contract\Container\Definition\ReferenceDefinition $reference
     *
     * @throws \Viserio\Contract\Container\Exception\NotFoundException
     *
     * @return string
     */
    private function compileReferenceDefinition(ReferenceDefinitionContract $reference): string
    {
        $id = $reference->getName();

        while ($this->containerBuilder->hasAlias($id)) {
            $id = $this->containerBuilder->getAlias($id)->getName();
        }

        if ($id === ContainerInterface::class) {
            return '$this';
        }

        if ($this->containerBuilder->hasDefinition($id)) {
            $definition = $this->containerBuilder->getDefinition($id);
            $uninitialized = $reference->getBehavior() === 2 /* ReferenceDefinitionContract::IGNORE_ON_UNINITIALIZED_REFERENCE */;

            if ($definition->isSynthetic()) {
                if ($uninitialized) {
                    $this->uninitializedServices[$id] = true;
                }

                $code = \sprintf('$this->get(%s)', $this->compileValue($id));
            } elseif ($uninitialized) {
                $code = 'null';

                if (! $definition->isShared()) {
                    return $code;
                }
            } elseif ($this->isTrivialInstance($definition)) {
                $code = $this->compileValue($definition);

                if (! isset($this->singleUsePrivateIds[$id]) && $definition->isShared()) {
                    $code = \sprintf('$this->%s[%s] = %s', $definition->isPublic() ? 'services' : 'privates', $this->compileValue($id), $code);
                }

                $code = "({$code})";
            } elseif ($this->asFiles && ! $this->inlineFactories && ! $this->isPreload($definition)) {
                $code = \sprintf('$this->load(\'get%s.php\')', $reference->getHash());
            } else {
                $code = \sprintf('$this->get%s()', $reference->getHash());
            }

            if (! isset($this->singleUsePrivateIds[$id]) && $definition->isShared()) {
                $code = \sprintf('($this->%s[%s] ?? %s)', $definition->isPublic() ? 'services' : 'privates', $this->compileValue($id), $code);
            }

            return $code;
        }

        if ($reference->getBehavior() === 2 /* ReferenceDefinitionContract::IGNORE_ON_UNINITIALIZED_REFERENCE */) {
            return 'null';
        }

        return \sprintf('($this->services[%s] ?? %s)', $this->compileValue($id), \sprintf('$this->get(%s)', $this->compileValue($id)));
    }

    /**
     * Check if the node is used only once.
     *
     * @param \Viserio\Contract\Container\ServiceReferenceGraphNode $node
     *
     * @return bool
     */
    private function isSingleUsePrivateNode(ServiceReferenceGraphNodeContract $node): bool
    {
        if ($node->getValue()->isPublic()) {
            return false;
        }

        $ids = [];

        foreach ($node->getInEdges() as $edge) {
            if (null === $value = $edge->getSourceNode()->getValue()) {
                continue;
            }

            if ($value instanceof AliasDefinition) {
                return false;
            }

            if ($edge->isLazy() || ! $value->isShared()) {
                return false;
            }

            $ids[$edge->getSourceNode()->getId()] = true;
        }

        return \count($ids) === 1;
    }

    /**
     * Returns the next available variable name to use.
     *
     * @return string
     */
    private function getNextVariableName(): string
    {
        $firstChars = DumperContract::FIRST_CHARS;
        $firstCharsLength = \strlen($firstChars);
        $nonFirstChars = DumperContract::NON_FIRST_CHARS;
        $nonFirstCharsLength = \strlen($nonFirstChars);

        while (true) {
            $name = '';
            $i = $this->variableCount;

            if ($name === '') {
                $name .= $firstChars[$i % $firstCharsLength];
                $i = (int) ($i / $firstCharsLength);
            }

            while ($i > 0) {
                $i--;
                $name .= $nonFirstChars[$i % $nonFirstCharsLength];
                $i = (int) ($i / $nonFirstCharsLength);
            }

            $this->variableCount++;

            // check that the name is not reserved
            if (\in_array($name, self::$reservedVariables, true)) {
                continue;
            }

            return $name;
        }
    }

    /**
     * Returns the service wrapped in a if condition.
     *
     * @param mixed  $value
     * @param string $code
     * @param bool   $isGenerator
     *
     *@throws \Viserio\Contract\Container\Exception\NotFoundException
     *
     * @return string
     */
    private function wrapServiceConditionals($value, string $code, bool $isGenerator = false): string
    {
        $condition = $this->getServiceConditionals($value);

        if ($condition === '') {
            return $code;
        }

        if ($isGenerator && $condition === 'false') {
            return '';
        }

        $eol = "\n";

        return $this->wrapInConditional($isGenerator ? "{$eol}{$code}{$eol}" : "{$eol}{$code}", $condition) . ($isGenerator ? '' : $eol);
    }

    /**
     * Returns the condition for the if wrap.
     *
     * @param mixed $value
     *
     *@throws \Viserio\Contract\Container\Exception\NotFoundException
     *
     * @return string
     */
    private function getServiceConditionals($value): string
    {
        $conditions = [];

        foreach (ContainerBuilder::getInitializedConditionals($value) as $service) {
            if (! $this->containerBuilder->hasDefinition($service)) {
                return 'false';
            }

            $conditions[] = \sprintf('isset($this->%s[%s])', $this->containerBuilder->getDefinition($service)->isPublic() ? 'services' : 'privates', $this->compileValue($service));
        }

        foreach (ContainerBuilder::getServiceConditionals($value) as $service) {
            if ($this->containerBuilder->hasDefinition($service) && ! $this->containerBuilder->getDefinition($service)->isPublic()) {
                continue;
            }

            $conditions[] = \sprintf('$this->has(%s)', $this->compileValue($service));
        }

        if (\count($conditions) === 0) {
            return '';
        }

        return \implode(' && ', $conditions);
    }

    /**
     * Compile a array to php code.
     *
     * @param array $values
     * @param bool  $skip
     *
     * @return string
     */
    private function compileArray(array $values, bool $skip = false): string
    {
        $code = [];

        $this->arraySpaceCount++;

        foreach ($values as $key => $v) {
            if ($skip && ! $this->containerBuilder->has($v->getName())) {
                continue;
            }

            if ($v instanceof stdClass) {
                $v = '(object) ' . $this->compileValue((array) $v);
            } else {
                $v = $this->compileValue($v);
            }

            $code[] = \sprintf(\str_repeat('    ', $this->arraySpaceCount) . '%s => %s,', $this->compileValue($key), $v);
        }

        $this->arraySpaceCount--;

        if (\count($code) === 0) {
            return '[]';
        }

        $eol = "\n";

        return \sprintf('[' . $eol . '%s' . $eol . \str_repeat('    ', $this->arraySpaceCount) . ']', \implode('' . $eol, $code));
    }

    /**
     * Compile parameters array to php code.
     *
     * @param array $values
     *
     * @return string
     */
    private function compileParameters(array $values): string
    {
        $code = [];
        $regex = '/$this->targetDirs\.\'\'/';
        $this->arraySpaceCount++;

        foreach ($values as $key => $value) {
            if (\is_array($value)) {
                $value = $this->compileParameters($value);
            } elseif ($value instanceof ParameterDefinition) {
                $value = $this->export($value->getValue());
            } else {
                $value = $this->export($value);
            }

            if (\preg_match($regex, $value)) {
                $value = \preg_replace($regex, \sprintf('\dirname(__DIR__, %d + $1)', (int) $this->asFiles), $value);
            }

            $code[] = \sprintf(\str_repeat('    ', $this->arraySpaceCount) . '%s => %s,', $this->export($key), $value);
        }

        $this->arraySpaceCount--;

        if (\count($code) === 0) {
            return '[]';
        }

        $eol = "\n";

        return \sprintf('[' . $eol . '%s' . $eol . \str_repeat('    ', $this->arraySpaceCount) . ']', \implode('' . $eol, $code));
    }

    /**
     * @return array
     */
    private function getPreparedRemovedIds(): array
    {
        $ids = $this->containerBuilder->getRemovedIds();

        foreach ($this->containerBuilder->getDefinitions() as $id => $definition) {
            if (! $definition->isPublic()) {
                $ids[$id] = true;
            }
        }

        $ids = \array_keys($ids);

        \sort($ids);

        return $ids;
    }

    /**
     * Check if the definition is a preload one.
     *
     * @param mixed $definition
     *
     * @return bool
     */
    private function isPreload($definition): bool
    {
        return self::$preloadCache[$definition->getName()] ?? self::$preloadCache[$definition->getName()] = ($this->preloadTag && $definition->hasTag($this->preloadTag) && ! $definition->isDeprecated());
    }

    /**
     * Wrap a code in a if condition.
     *
     * @param string $code
     * @param string $condition
     *
     * @return string
     */
    private function wrapInConditional(string $code, string $condition): string
    {
        $eol = "\n";

        // re-indent the wrapped code
        $code = \implode($eol, \array_map(static function ($line) {
            return $line ? '    ' . $line : $line;
        }, \explode($eol, $code)));

        $asFile = $this->asFiles && ! $this->inlineFactories;
        $beforeSpace = '';

        if ($this->wrapConditionCalled === false) {
            $this->wrapConditionCalled = true;
        } else {
            $beforeSpace = $eol;
        }

        return \sprintf('%sif (%s) {%s%s}', $beforeSpace . ($asFile ? '' : '        '), $condition, $code, $asFile ? '' : '        ');
    }

    /**
     * @param \Viserio\Contract\Container\Definition\Definition $inlineDef
     * @param string                                            $name
     * @param bool                                              $isProxy
     * @param null|string                                       $sharedNonLazyId
     *
     *@throws \Viserio\Contract\Container\Exception\NotFoundException
     *
     * @return string
     */
    private function addDefinitionCondition($inlineDef, string $name, bool $isProxy, ?string $sharedNonLazyId): string
    {
        $code = '';
        $eol = "\n";

        foreach ($inlineDef->getConditions() as $conditionArgument) {
            $condition = '';

            foreach ($conditionArgument->getValue() as $value) {
                if (\is_string($value)) {
                    $condition .= $value;
                } else {
                    $condition .= $this->compileValue($value);
                }
            }

            $tmpDefinition = new class($inlineDef->isPublic()) extends ConditionDefinition {
                /**
                 * Check if the service is public.
                 *
                 * @var bool
                 */
                private $isPublic;

                /**
                 * @param bool $isPublic
                 */
                public function __construct(bool $isPublic)
                {
                    $this->isPublic = $isPublic;
                }

                /**
                 * {@inheritdoc}
                 */
                public function isPublic(): bool
                {
                    return $this->isPublic;
                }
            };

            $conditionCode = '';

            $conditionArgument->getCallback()($tmpDefinition);

            if ($inlineDef instanceof PropertiesAwareDefinitionContract) {
                $tmpDefinition->setClass($inlineDef->getClass());

                $conditionCode .= $this->addServiceProperties($tmpDefinition, $name, $isProxy);
            }

            if ($inlineDef instanceof MethodCallsAwareDefinitionContract) {
                $conditionCode .= $this->addServiceMethodCalls($tmpDefinition, $name, $sharedNonLazyId, $isProxy);
            }

            if ($conditionCode !== '') {
                $code .= $this->wrapInConditional($eol . $conditionCode, $condition) . $eol;
            }

            $tmpDefinition = null; // reset
        }

        return $code;
    }

    /**
     * @param \Viserio\Contract\Container\Definition\Definition $definition
     *
     *@throws \Viserio\Contract\Container\Exception\NotFoundException
     * @throws \Viserio\Contract\Container\Exception\CircularDependencyException
     *
     * @return bool
     */
    private function isTrivialInstance($definition): bool
    {
        if ($definition->isSynthetic() || ($definition instanceof MethodCallsAwareDefinitionContract && \count($definition->getMethodCalls()) !== 0) || ($definition instanceof PropertiesAwareDefinitionContract && \count($definition->getProperties()) !== 0) || \in_array($definition->getType(), [3 /* DefinitionContract::PRIVATE */, 4 /* DefinitionContract::SERVICE + DefinitionContract::PRIVATE */, 5 /* DefinitionContract::SINGLETON + DefinitionContract::PRIVATE */], true)) {
            return false;
        }

        if ($definition->isDeprecated() || $definition->isLazy()) {
            return false;
        }

        if ($definition instanceof ArgumentAwareDefinitionContract) {
            $args = $definition->getArguments();

            if (3 < \count($args)) {
                return false;
            }

            foreach ($args as $arg) {
                if (! $arg || $arg instanceof ParameterDefinition) {
                    continue;
                }

                if (\is_array($arg) && 3 >= \count($arg)) {
                    foreach ($arg as $k => $v) {
                        if (! $v || $v instanceof ParameterDefinition) {
                            continue;
                        }

                        if ($v instanceof ReferenceDefinitionContract && $this->containerBuilder->has($id = $v->getName()) && $this->containerBuilder->findDefinition($id)->isSynthetic()) {
                            continue;
                        }

                        if (! \is_scalar($v)) {
                            return false;
                        }
                    }
                } elseif ($arg instanceof ReferenceDefinitionContract && $this->containerBuilder->has($id = $arg->getName()) && $this->containerBuilder->findDefinition($id)->isSynthetic()) {
                    continue;
                } elseif (! \is_scalar($arg)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Validate the given dumper options.
     *
     * @param array $options
     *
     * @return void
     */
    private function validateDumperOptions(array $options): void
    {
        $validators = [
            'base_class' => ['\is_string'],
            'build_time' => ['\is_int'],
            'class' => ['\is_string'],
            'debug' => ['\is_bool'],
            'file' => ['\is_string', '\is_null'],
            'namespace' => ['\is_string', '\is_null'],
            'as_files_parameter' => ['\is_string'],
            'inline_class_loader_parameter' => ['\is_string'],
            'inline_factories_parameter' => ['\is_string'],
            'preload_tag' => ['\is_string'],
        ];

        foreach ($options as $key => $option) {
            $hasError = true;

            if (! isset($validators[$key])) {
                throw new InvalidArgumentException(\sprintf('[%s] is not available as option in [%s] class.', $key, self::class));
            }

            foreach ($validators[$key] ?? [] as $validator) {
                if ($hasError) {
                    $hasError = ! $validator($option);
                }
            }

            if ($hasError) {
                throw new InvalidArgumentException(\sprintf('Invalid configuration value provided for [%s]; Expected [%s], but got [%s].', $key, \count($validators[$key]) === 1 ? \str_replace('\is_', '', $validators[$key][0]) : \implode('] or [', $validators[$key]), (\is_object($option) ? \get_class($option) : \gettype($option)), ));
            }
        }
    }

    /**
     * Get correct class from getClass call.
     *
     * @param \Viserio\Contract\Container\Definition\FactoryDefinition|\Viserio\Contract\Container\Definition\ObjectDefinition $definition
     *
     *@throws \Viserio\Contract\Container\Exception\NotFoundException
     * @throws \Viserio\Contract\Container\Exception\CircularDependencyException
     *
     * @return string
     */
    private function getCorrectLineageClass($definition): string
    {
        $class = $definition->getClass();
        $value = $definition->getValue();

        if (\is_array($value)) {
            $value = $value[0];
        }

        if ($class === ReferenceDefinition::class) {
            $value = $this->containerBuilder->findDefinition($value->getName());

            if (! $value instanceof ObjectDefinition && ! $value instanceof FactoryDefinition) {
                return '';
            }

            $class = $value->getClass();
        } elseif ($class === ObjectDefinition::class) {
            $class = $value->getClass();
        }

        return $class;
    }

    /**
     * Normalize path.
     *
     * @param string $path
     *
     * @throws \LogicException
     *
     * @return string
     */
    private static function normalizePath($path): string
    {
        $normalized = \str_replace('\\', '/', $path);
        // Remove any kind of funky unicode whitespace
        $normalized = \preg_replace('#\p{C}+|^\./#u', '', $normalized);

        return \preg_replace('#\\\{2,}#', '\\', \trim($normalized, '\\'));
    }
}
