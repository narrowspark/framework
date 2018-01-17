<?php
declare(strict_types=1);
namespace Viserio\Component\Support;

use Closure;
use Viserio\Component\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contract\Support\Exception\InvalidArgumentException;
use Viserio\Component\Contract\Support\Manager as ManagerContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

abstract class AbstractManager implements
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract,
    ManagerContract
{
    use ContainerAwareTrait;
    use OptionsResolverTrait;

    /**
     * Default name for the drivers config.
     *
     * @var string
     */
    protected const DRIVERS_CONFIG_LIST_NAME = 'drivers';

    /**
     * The array of created "drivers".
     *
     * @var array
     */
    protected $drivers = [];

    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $extensions = [];

    /**
     * Resolved options.
     *
     * @var array
     */
    protected $resolvedOptions = [];

    /**
     * Create a new manager instance.
     *
     * @param iterable|\Psr\Container\ContainerInterface $data
     */
    public function __construct($data)
    {
        $this->resolvedOptions = self::resolveOptions($data);
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return \call_user_func_array([$this->getDriver(), $method], $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', static::getConfigName()];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): iterable
    {
        return [self::DRIVERS_CONFIG_LIST_NAME];
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        return $this->resolvedOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultDriver(): string
    {
        return $this->resolvedOptions['default'];
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultDriver(string $name): void
    {
        $this->resolvedOptions['default'] = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getDriver(?string $driver = null)
    {
        $driver = $driver ?? $this->getDefaultDriver();

        // If the given driver has not been created before, we will create the instances
        // here and cache it so we can return it next time very quickly. If there is
        // already a driver created by this name, we'll just return that instance.
        if (! isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver(
                $this->getDriverConfig($driver)
            );
        }

        return $this->drivers[$driver];
    }

    /**
     * {@inheritdoc}
     */
    public function extend(string $driver, Closure $callback): void
    {
        $this->extensions[$driver] = $callback->bindTo($this, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function getDrivers(): array
    {
        return $this->drivers;
    }

    /**
     * {@inheritdoc}
     */
    public function hasDriver(string $driver): bool
    {
        $method = 'create' . Str::studly($driver) . 'Driver';

        return \method_exists($this, $method) || isset($this->extensions[$driver]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDriverConfig(string $name): array
    {
        $name = $name ?? $this->getDefaultDriver();

        $drivers = $this->resolvedOptions[self::DRIVERS_CONFIG_LIST_NAME] ?? [];

        if (isset($drivers[$name]) && \is_array($drivers[$name])) {
            $config         = $drivers[$name];
            $config['name'] = $name;

            return $config;
        }

        return ['name' => $name];
    }

    /**
     * {@inheritdoc}
     */
    public function createDriver(array $config)
    {
        $method = 'create' . Str::studly($config['name']) . 'Driver';

        if (isset($this->extensions[$config['name']])) {
            return $this->callCustomCreator($config['name'], $config);
        }

        if (\method_exists($this, $method)) {
            return $this->$method($config);
        }

        throw new InvalidArgumentException(\sprintf('Driver [%s] not supported.', $config['name']));
    }

    /**
     * Call a custom driver creator.
     *
     * @param string $driver
     * @param array  $config
     *
     * @return mixed
     */
    protected function callCustomCreator(string $driver, array $config = [])
    {
        return $this->extensions[$driver]($config);
    }

    /**
     * Get the configuration name.
     *
     * @return string
     */
    abstract protected static function getConfigName(): string;
}
