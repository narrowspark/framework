<?php
declare(strict_types=1);
namespace Viserio\Component\Container;

use Viserio\Component\Container\Compiler\CompiledContainer;
use Viserio\Component\Container\Compiler\Compiler;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Container\Exception\InvalidArgumentException;

class ContainerBuilder extends Container
{
    /**
     * Name of the container class, used to create the container.
     *
     * @var string
     */
    private $containerClass = 'CompiledContainer';

    /**
     * Name of the container parent class, used on compiled container.
     *
     * @var string
     */
    private $containerParentClass = CompiledContainer::class;

    /**
     * Namespace of the container class, used on compiled container.
     *
     * @var string
     */
    private $containerNamespace = 'Viserio\Component\Container';

    /**
     * If true, write the proxies to disk to improve performances.
     *
     * @var bool
     */
    private $writeProxiesToFile = false;

    /**
     * Directory where to write the proxies (if $writeProxiesToFile is enabled).
     *
     * @var null|string
     */
    private $proxyDirectory;

    /**
     * @var null|string
     */
    private $compileToDirectory;

    /**
     * Whether the container has already been built.
     *
     * @var bool
     */
    private $locked = false;

    /**
     * Configure the proxy generation.
     *
     * For dev environment, use `writeProxiesToFile(false)` (default configuration)
     * For production environment, use `writeProxiesToFile(true, 'tmp/proxies')`
     *
     * @param bool        $writeToFile    If true, write the proxies to disk to improve performances
     * @param null|string $proxyDirectory Directory where to write the proxies
     *
     * @throws \Viserio\Component\Contract\Container\Exception\InvalidArgumentException when writeToFile is set to true and the proxy directory is null
     *
     * @return void
     */
    public function writeProxiesToFile(bool $writeToFile, string $proxyDirectory = null): void
    {
        $this->ensureNotLocked();

        $this->writeProxiesToFile = $writeToFile;

        if ($writeToFile === true && $proxyDirectory === null) {
            throw new InvalidArgumentException(
                'The proxy directory must be specified if you want to write proxies on disk.'
            );
        }

        $this->proxyDirectory = $proxyDirectory;
    }

    /**
     * Compile the container for optimum performances.
     *
     * Be aware that the container is compiled once and never updated!
     *
     * Therefore:
     * - in production you should clear that directory every time you deploy
     * - in development you should not compile the container
     *
     * @param string $cacheDirectory       Directory in which to put the compiled container
     * @param string $containerClass       Name of the compiled class. Customize only if necessary.
     * @param string $containerParentClass Name of the compiled container parent class. Customize only if necessary.
     * @param string $containerNamespace   namespace of the compiled container
     *
     * @return \Viserio\Component\Container\ContainerBuilder
     */
    public function enableCompilation(
        string $cacheDirectory,
        string $containerClass = 'CompiledContainer',
        string $containerParentClass = CompiledContainer::class,
        string $containerNamespace = 'Viserio\Component\Container'
    ): self {
        $this->ensureNotLocked();

        $this->compileToDirectory   = $cacheDirectory;
        $this->containerClass       = $containerClass;
        $this->containerParentClass = $containerParentClass;
        $this->containerNamespace   = $containerNamespace;

        return $this;
    }

    /**
     * Build and return a container.
     *
     * @return \Viserio\Component\Contract\Container\Container||\Viserio\Component\Contract\Container\TaggedContainer
     */
    public function build(): ContainerContract
    {
        $this->locked  = true;
        $className     = \ltrim($this->containerClass, '\\');
        $namespace     = $this->containerNamespace !== null ? ('\\' . \ltrim(\rtrim($this->containerNamespace, '\\'), '\\')) : '';
        $fullClassName = $namespace . '\\' . $className;

        if ($this->compileToDirectory) {
            $compiler              = new Compiler();
            $compiledContainerFile = $compiler->compile(
                $this->compileToDirectory,
                $this->getBindings(),
                $this->extenders,
                [
                    'parent_class' => $this->containerParentClass,
                    'build_time'   => \time(),
                    'class'        => $className,
                    'namespace'    => $this->containerNamespace,
                ]
            );
            // Only load the file if it hasn't been already loaded
            // (the container can be created multiple times in the same process)
            if (! \class_exists($fullClassName, false)) {
                require $compiledContainerFile;
            }
        }

        return new $fullClassName($this->bindings);
    }

    /**
     * Check if the container was build.
     *
     * @throws \LogicException
     */
    private function ensureNotLocked(): void
    {
        if ($this->locked) {
            throw new \LogicException('The Compiler cannot be modified after the container has been built.');
        }
    }
}
