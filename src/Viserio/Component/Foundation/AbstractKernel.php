<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation;

use ReflectionObject;
use Viserio\Component\Contracts\Container\Container as ContainerContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Contracts\Foundation\Terminable as TerminableContract;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

abstract class AbstractKernel implements
    KernelContract,
    TerminableContract,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract,
    RequiresMandatoryOptionsContract
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * The kernel version.
     *
     * @var string
     */
    public const VERSION = '1.0.0-DEV';

    /**
     * The kernel version id.
     *
     * @var int
     */
    public const VERSION_ID  = 10000;

    /**
     * The kernel extra version.
     *
     * @var string
     */
    public const EXTRA_VERSION = 'DEV';

    /**
     * Container instance.
     *
     * @var \Viserio\Component\Contracts\Container\Container
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'app'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMandatoryOptions(): iterable
    {
        return [
            'routing' => [
                'path',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): iterable
    {
        return [
            'env'         => 'production',
            'middlewares' => [
                'skip' => false,
            ],
        ];
    }

    /**
     * Set a container instance.
     *
     * @param \Viserio\Component\Contracts\Container\Container $container
     *
     * @return $this
     */
    public function setContainer(ContainerContract $container): self
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the container instance.
     *
     * @throws \RuntimeException
     *
     * @return \Viserio\Component\Contracts\Container\Container
     */
    public function getContainer(): ContainerContract
    {
        return $this->container;
    }

    /**
     * Gets the application root dir (path of the project's composer file).
     *
     * @return string The project root dir
     */
    public function getProjectDir(): string
    {
        if ($this->projectDir === null) {
            $reflection = new ReflectionObject($this);
            $dir        = $rootDir        = dirname($reflection->getFileName());

            while (! file_exists($dir . '/composer.json')) {
                if (dirname($dir) === $dir) {
                    return $this->projectDir = $rootDir;
                }

                $dir = dirname($dir);
            }

            $this->projectDir = $dir;
        }

        return $this->projectDir;
    }

    /**
     * Get the path to the application "app" directory.
     *
     * @param string $path Optionally, a path to append to the app path
     *
     * @return string
     */
    public function getAppPath($path = ''): string
    {
        return $this->normalizeDirectorySeparator(
            $this->getProjectDir() . '/app' . ($path ? '/' . $path : $path)
        );
    }

    /**
     * Get the path to the bootstrap directory.
     *
     * @param string $path Optionally, a path to append to the bootstrap path
     *
     * @return string
     */
    public function getBootstrapPath($path = '')
    {
        return $this->normalizeDirectorySeparator(
            $this->getProjectDir() . '/bootstrap' . ($path ? '/' . $path : $path)
        );
    }

    /**
     * Get the path to the application configuration files.
     *
     * @param string $path Optionally, a path to append to the config path
     *
     * @return string
     */
    public function getConfigPath($path = '')
    {
        return $this->normalizeDirectorySeparator(
            $this->getProjectDir() . '/config' . ($path ? '/' . $path : $path)
        );
    }

    /**
     * Get the path to the database directory.
     *
     * @param string $path Optionally, a path to append to the database path
     *
     * @return string
     */
    public function getDatabasePath($path = '')
    {
        return $this->normalizeDirectorySeparator(
            ($this->databasePath ?: $this->getProjectDir() . '/database') . ($path ? '/' . $path : $path)
        );
    }
}
