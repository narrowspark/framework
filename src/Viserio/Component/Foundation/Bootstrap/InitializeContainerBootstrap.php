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

namespace Viserio\Component\Foundation\Bootstrap;

use PhpParser\Lexer\Emulative;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use ReflectionClass;
use Symfony\Component\Debug\DebugClassLoader;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Dumper\PhpDumper;
use Viserio\Component\Container\PhpParser\PrettyPrinter;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Exception\RuntimeException;
use Viserio\Contract\Foundation\Bootstrap as BootstrapContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;

class InitializeContainerBootstrap implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public static function getPriority(): int
    {
        return 256;
    }

    /**
     * {@inheritdoc}
     */
    public static function bootstrap(KernelContract $kernel): void
    {
        $environment = $kernel->getEnvironment();
        $cacheDir = $kernel->getStoragePath('framework' . \DIRECTORY_SEPARATOR . 'container' . \DIRECTORY_SEPARATOR . $environment);
        $logDir = $kernel->getStoragePath('logs' . \DIRECTORY_SEPARATOR . $environment);

        $class = static::getContainerClass($kernel);
        $containerFile = $cacheDir . \DIRECTORY_SEPARATOR . $class . '.php';
        $isDebug = $kernel->isDebug();
        $isFresh = false;
        $oldContainer = null;

        if ($isFresh = (! $isDebug && \is_file($containerFile))) {
            // Silence E_WARNING to ignore "include" failures - don't use "@" to prevent silencing fatal errors
            $errorLevel = \error_reporting(\E_ALL ^ \E_WARNING);

            try {
                if (\file_exists($containerFile) && \is_object($container = $kernel->setContainer(include $containerFile)->getContainer())) {
                    $container->set(KernelContract::class, $kernel);
                }
            } catch (Throwable $exception) {
            } finally {
                \error_reporting($errorLevel);
            }
        }

        $collectedLogs = [];
        $previousHandler = false;

        if ($isDebug) {
            $previousHandler = static::collectContainerLogs($collectedLogs);
        }

        try {
            $container = null;
            /** @var \Viserio\Contract\Container\ContainerBuilder $container */
            $container = static::buildContainer($cacheDir, $logDir, $kernel);
            $container->compile();
        } finally {
            if ($isDebug && $previousHandler !== true) {
                \restore_error_handler();

                \file_put_contents($cacheDir . \DIRECTORY_SEPARATOR . $class . 'Deprecations.log', \serialize(\array_values($collectedLogs)));
                \file_put_contents($cacheDir . \DIRECTORY_SEPARATOR . $class . 'Compiler.log', $container !== null ? \implode("\n", $container->getLogs()) : '');
            }
        }

        if (! $isFresh && \file_exists($containerFile)) {
            $errorLevel = \error_reporting(\E_ALL ^ \E_WARNING);

            try {
                $oldContainer = include $containerFile;
            } catch (Throwable $exception) {
            } finally {
                \error_reporting($errorLevel);
            }
        }

        $oldContainer = \is_object($oldContainer) ? new ReflectionClass($oldContainer) : null;

        self::dumpContainer($containerFile, $container, $class, $kernel);

        require $containerFile;

        $container = new $class();
        $container->set(KernelContract::class, $kernel);

        $kernel->setContainer($container);

        if ($oldContainer && \get_class($container) !== $oldContainer->name) {
            // Because concurrent requests might still be using them,
            // old container files are not removed immediately,
            // but on a next dump of the container.
            static $legacyContainers = [];
            $oldContainerDir = \dirname($oldContainer->getFileName());
            $legacyContainers[$oldContainerDir . '.legacy'] = true;

            foreach (\glob(\dirname($oldContainerDir) . \DIRECTORY_SEPARATOR . '*.legacy') as $legacyContainer) {
                if (! isset($legacyContainers[$legacyContainer]) && @\unlink($legacyContainer)) {
                    (new Filesystem())->remove(\substr($legacyContainer, 0, -7));
                }
            }

            \touch($oldContainerDir . '.legacy');
        }
    }

    /**
     * Gets the container class.
     *
     * @param \Viserio\Contract\Foundation\Kernel $kernel
     *
     * @return string The container class
     */
    protected static function getContainerClass(KernelContract $kernel): string
    {
        $class = \get_class($kernel);
        $class = \strpos($class, 'c') === 0 && \strpos($class, "class@anonymous\0") === 0 ? \get_parent_class($class) . \str_replace('.', '_', ContainerBuilder::getHash($class)) : $class;

        return \str_replace('\\', '_', $class) . \ucfirst($kernel->getEnvironment()) . ($kernel->isDebug() ? 'Debug' : '') . 'Container';
    }

    /**
     * Builds the service container.
     *
     * @param string                              $cacheDir
     * @param string                              $logDir
     * @param \Viserio\Contract\Foundation\Kernel $kernel
     *
     * @throws \RuntimeException
     *
     * @return \Viserio\Contract\Container\ContainerBuilder & \Viserio\Contract\Container\ServiceProvider\ContainerBuilder The compiled service container
     */
    protected static function buildContainer(
        string $cacheDir,
        string $logDir,
        KernelContract $kernel
    ): ContainerBuilderContract {
        foreach (['cache' => $cacheDir, 'logs' => $logDir] as $name => $dir) {
            if (! \is_dir($dir)) {
                if (false === @\mkdir($dir, 0777, true) && ! \is_dir($dir)) {
                    throw new RuntimeException(\sprintf("Unable to create the %s directory (%s)\n", $name, $dir));
                }
            } elseif (! \is_writable($dir)) {
                throw new RuntimeException(\sprintf("Unable to write in the %s directory (%s)\n", $name, $dir));
            }
        }

        return $kernel->getContainerBuilder();
    }

    /**
     * Dumps the service container to PHP code in the cache.
     *
     * @param string                                       $containerFile The container path
     * @param \Viserio\Contract\Container\ContainerBuilder $container     The service container
     * @param string                                       $class         The name of the class to generate
     * @param KernelContract                               $kernel
     *
     * @throws \Viserio\Contract\Container\Exception\CircularDependencyException
     *
     * @return void
     */
    protected static function dumpContainer(
        string $containerFile,
        ContainerBuilderContract $container,
        string $class,
        KernelContract $kernel
    ): void {
        $hasPhpParser = \class_exists(Standard::class);

        $dumper = new PhpDumper(
            $container,
            $hasPhpParser ? (new ParserFactory())->create(
                ParserFactory::ONLY_PHP7,
                new Emulative([
                    'usedAttributes' => ['comments', 'startLine', 'endLine', 'startFilePos', 'endFilePos'],
                ])
            ) : null,
            $hasPhpParser ? new PrettyPrinter() : null
        );

        if (($proxyDumper = $kernel->getProxyDumper()) !== null) {
            $dumper->setProxyDumper($proxyDumper);
        }

        $content = $dumper->dump([
            'class' => $class,
            'base_class' => $kernel->getContainerBaseClass(),
            'debug' => $kernel->isDebug(),
            'file' => $containerFile,
            'build_time' => $container->has('kernel.container_build_time') ? $container->get('kernel.container_build_time') : \time(),
        ]);

        $filesystem = new Filesystem();

        if (\is_array($content)) {
            $rootCode = \array_pop($content);
            $dir = \dirname($containerFile) . \DIRECTORY_SEPARATOR;

            foreach ($content as $file => $code) {
                $filesystem->dumpFile($dir . $file, $code);

                @\chmod($dir . $file, 0666 & ~\umask());
            }

            $legacyFile = \dirname($dir . $file) . '.legacy';

            if (\file_exists($legacyFile)) {
                @\unlink($legacyFile);
            }
        } else {
            $rootCode = $content;
        }

        $filesystem->dumpFile($containerFile, $rootCode);

        try {
            $filesystem->chmod($containerFile, 0666, \umask());
        } catch (IOException $e) {
            // discard chmod failure (some filesystem may not support it)
        }

        if (\function_exists('opcache_invalidate') && \filter_var(\ini_get('opcache.enable'), \FILTER_VALIDATE_BOOLEAN)) {
            @\opcache_invalidate($containerFile, true);
        }
    }

    /**
     * @param mixed $collectedLogs
     *
     * @return null|array
     */
    protected static function collectContainerLogs(&$collectedLogs): ?array
    {
        $previousHandler = \set_error_handler(static function ($type, $message, $file, $line) use (&$collectedLogs, &$previousHandler) {
            if (\E_USER_DEPRECATED !== $type && \E_DEPRECATED !== $type) {
                return $previousHandler ? $previousHandler($type, $message, $file, $line) : false;
            }

            if (isset($collectedLogs[$message])) {
                $collectedLogs[$message]['count']++;

                return;
            }

            $backtrace = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 5);

            // Clean the trace by removing first frames added by the error handler itself.
            for ($i = 0; isset($backtrace[$i]); $i++) {
                if (isset($backtrace[$i]['file'], $backtrace[$i]['line']) && $backtrace[$i]['line'] === $line && $backtrace[$i]['file'] === $file) {
                    $backtrace = \array_slice($backtrace, 1 + $i);

                    break;
                }
            }

            // Remove frames added by DebugClassLoader.
            for ($i = \count($backtrace) - 2; 0 < $i; $i--) {
                if (DebugClassLoader::class === ($backtrace[$i]['class'] ?? null)) {
                    $backtrace = [$backtrace[$i + 1]];

                    break;
                }
            }

            $collectedLogs[$message] = [
                'type' => $type,
                'message' => $message,
                'file' => $file,
                'line' => $line,
                'trace' => [$backtrace[0]],
                'count' => 1,
            ];
        });

        return $previousHandler;
    }
}
