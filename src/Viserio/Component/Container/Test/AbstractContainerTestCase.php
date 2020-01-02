<?php

declare(strict_types=1);

namespace Viserio\Component\Container\Test;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Dumper\PhpDumper;
use Viserio\Component\Container\PipelineConfig;
use Viserio\Component\Container\Test\Pipeline\TestContainerPipe;

/**
 * @internal
 */
abstract class AbstractContainerTestCase extends TestCase
{
    /**
     * Refresh container on every build.
     *
     * @var bool
     */
    protected const REFRESH_CONTAINER = false;

    /**
     * Dump the container only for the class.
     *
     * If you choose false you need to manually prepare and dump the container.
     *
     * @var bool
     */
    protected const DUMP_CLASS_CONTAINER = true;

    /** @var bool */
    protected const SKIP_TEST_PIPE = false;

    /** @var \Viserio\Component\Container\ContainerBuilder */
    protected $containerBuilder;

    /** @var \Viserio\Contract\Container\CompiledContainer */
    protected $container;

    /** @var \Viserio\Contract\Container\LazyProxy\Dumper */
    protected $proxyDumper;

    /** @var \PhpParser\Parser */
    protected $phpParser;

    /** @var \PhpParser\PrettyPrinter\Standard */
    protected $prettyPrinter;

    /**
     * Extend the dumper with more different settings.
     *
     * @var array
     */
    protected $dumperOptions = [];

    /**
     * @var string
     */
    private $currentDumpedContainerPath;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $containerBuilder = new ContainerBuilder();

        if (! static::SKIP_TEST_PIPE) {
            $containerBuilder->getPipelineConfig()
                ->addPipe(new TestContainerPipe(), PipelineConfig::TYPE_BEFORE_REMOVING, -32);
        }

        if (static::DUMP_CLASS_CONTAINER) {
            $this->prepareContainerBuilder($containerBuilder);

            $containerBuilder->compile();
        }

        $this->containerBuilder = $containerBuilder;

        if (static::DUMP_CLASS_CONTAINER) {
            $this->dumpContainer(null);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->containerBuilder = $this->proxyDumper = $this->phpParser = $this->prettyPrinter = $this->container = null;
        $this->dumperOptions = [];
    }

    /**
     * Prepare the container, this is only used if the DUMP_CLASS_CONTAINER is set to true.
     *
     * @param \Viserio\Component\Container\ContainerBuilder $containerBuilder
     *
     * @return void
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
    }

    /**
     * Returns the dump folder path for the container.
     *
     * @return string
     */
    abstract protected function getDumpFolderPath(): string;

    /**
     * Returns the container namespace.
     *
     * @return string
     */
    abstract protected function getNamespace(): string;

    /**
     * Get the class name.
     *
     * @throws \ReflectionException
     *
     * @return string
     */
    protected function getClassName(): string
    {
        $shortClassName = (new \ReflectionClass($this))->getShortName();

        return \ucfirst($shortClassName);
    }

    /**
     * Dump the container to file.
     *
     * @param null|string $functionName
     *
     * @throws \ReflectionException
     * @throws \Viserio\Contract\Container\Exception\CircularDependencyException
     *
     * @return void
     */
    protected function dumpContainer(?string $functionName): void
    {
        $className = $this->getDumperContainerClassName($functionName);
        $dirPath = \rtrim($this->getDumpFolderPath(), \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR;
        $fullContainerPath = $dirPath . $className . '.php';

        if (static::REFRESH_CONTAINER && \file_exists($fullContainerPath)) {
            @\unlink($fullContainerPath);
            @\rmdir($dirPath);
        }

        $namespace = $this->getNamespace();

        if (! \file_exists($fullContainerPath)) {
            $asFile = $this->containerBuilder->hasParameter('container.dumper.as_files') ? $this->containerBuilder->getParameter('container.dumper.as_files') : false;
            $content = $this->getPhpDumper()->dump(\array_merge(
                [
                    'class' => $className,
                    'namespace' => $this->getNamespace(),
                ],
                ($asFile ? ['file' => $fullContainerPath] : []),
                $this->dumperOptions
            ));

            if (\is_string($content)) {
                PhpDumper::dumpCodeToFile($fullContainerPath, $content);
            } else {
                foreach ($content as $file => $code) {
                    PhpDumper::dumpCodeToFile($dirPath . $file, $code);
                }
            }
        }

        $this->currentDumpedContainerPath = $fullContainerPath;

        require $fullContainerPath;

        $fullClassName = $namespace . '\\' . $className;
        $this->container = new $fullClassName();
    }

    /**
     * Assert if the dumped container is the same.
     *
     * @param null|string $functionName
     *
     * @throws \ReflectionException
     * @throws \Viserio\Contract\Container\Exception\CircularDependencyException
     *
     * @return void
     */
    protected function assertDumpedContainer(?string $functionName): void
    {
        $className = $this->getDumperContainerClassName($functionName);

        self::assertStringEqualsFile(
            \rtrim($this->getDumpFolderPath(), \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR . $className . '.php',
            $this->getPhpDumper()->dump(\array_merge(
                [
                    'class' => $className,
                    'namespace' => $this->getNamespace(),
                ],
                $this->dumperOptions
            ))
        );
    }

    /**
     * @return \Viserio\Component\Container\Dumper\PhpDumper
     */
    protected function getPhpDumper(): PhpDumper
    {
        $phpDumper = new PhpDumper($this->containerBuilder, $this->phpParser, $this->prettyPrinter);

        if ($this->proxyDumper !== null) {
            $phpDumper->setProxyDumper($this->proxyDumper);
        }

        return $phpDumper;
    }

    /**
     * @param null|string $functionName
     *
     * @throws \ReflectionException
     *
     * @return mixed|string
     */
    protected function getDumperContainerClassName(?string $functionName)
    {
        $className = \str_replace('Test', 'Container', $this->getClassName());

        if ($functionName !== null) {
            $className .= \ucfirst($functionName);
        }

        return $className;
    }
}
