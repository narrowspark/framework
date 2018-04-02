<?php
declare(strict_types=1);
namespace Viserio\Component\Discovery;

final class Package
{
    /**
     * @var string
     */
    public const CONFIGURE = 'configure';

    /**
     * @var string
     */
    public const UNCONFIGURE = 'unconfigure';

    /**
     * The package name.
     *
     * @var string
     */
    private $name;

    /**
     * The package version.
     *
     * @var string
     */
    private $version;

    /**
     * The package extra config for narrowspark.
     *
     * @var array
     */
    private $packageConfig;

    /**
     * Path to the composer vendor dir.
     *
     * @var string
     */
    private $vendorPath;

    /**
     * Create a new Package instance.
     *
     * @param string $name
     * @param string $vendorDirPath
     * @param array  $packageConfig
     */
    public function __construct(string $name, string $vendorDirPath, array $packageConfig)
    {
        $this->name       = $name;
        $this->vendorPath = $vendorDirPath;
        $this->version    = $packageConfig['package_version'];

        unset($packageConfig['package_version']);

        $this->packageConfig = $packageConfig;
    }

    /**
     * Get the package name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the package version.
     *
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getPackagePath(): string
    {
        return \strtr($this->vendorPath . '/' . $this->name . '/', '\\', '/');
    }

    /**
     * Checks if configurator key exits in extra narrowspark config.
     *
     * @param string $key
     * @param string $type
     *
     * @return bool
     */
    public function hasConfiguratorKey(string $key, string $type): bool
    {
        return \array_key_exists($key, $this->packageConfig[$type]);
    }

    /**
     * Returns the needed options for the right configurator.
     *
     * @param string $key
     * @param string $type
     *
     * @return array
     */
    public function getConfiguratorOptions($key, $type): array
    {
        if ($this->hasConfiguratorKey($key, $type)) {
            return (array) $this->packageConfig[$type][$key];
        }

        return [];
    }

    /**
     * Returns the extra config for narrowspark.
     *
     * @return array
     */
    public function getExtraOptions(): array
    {
        return $this->packageConfig;
    }
}
