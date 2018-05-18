<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Compiler;

use Viserio\Component\Container\Container;

final class Compiler
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
    private $containerParentClass = Container::class;

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
     * Whether the container has already been built.
     *
     * @var bool
     */
    private $locked = false;

    /**
     * Path to the compile dir.
     *
     * @var null|string
     */
    private $compileToDirectory;

    /**
     * Compile the container.
     *
     * @param array $options
     *
     * @return string the compiled container file name
     */
    public function compile(array $options = []): string
    {
        $options = \array_merge([
            'base_class' => $this->containerParentClass,
            'build_time' => \time(),
            'class'      => $this->containerClass,
            'namespace'  => 'Viserio\Component\Container',
        ], $options);
    }
}
