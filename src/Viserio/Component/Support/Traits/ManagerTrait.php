<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Traits;

use Closure;
use Viserio\Component\Contract\Support\Exception\InvalidArgumentException;

/** @internal */
trait ManagerTrait
{
    /**
     * The registered custom driver / connections creators.
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
        return [static::CONFIG_LIST_NAME];
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
    public function extend(string $driver, Closure $callback): void
    {
        $this->extensions[$driver] = $callback->bindTo($this, $this);
    }

    /**
     * Call a custom connection / driver creator.
     *
     * @param string $extension
     * @param array  $config
     *
     * @return mixed
     */
    protected function callCustomCreator(string $extension, array $config = [])
    {
        return $this->extensions[$extension]($config);
    }

    /**
     * Get config on adapter name.
     *
     * @param string $name
     *
     * @return array
     */
    protected function getConfigFromName(string $name): array
    {
        $adapter = $this->resolvedOptions[static::CONFIG_LIST_NAME] ?? [];

        if (isset($adapter[$name]) && \is_array($adapter[$name])) {
            $config         = $adapter[$name];
            $config['name'] = $name;

            return $config;
        }

        return ['name' => $name];
    }

    /**
     * Make a new driver instance.
     *
     * @param array  $config
     * @param string $method
     * @param string $errorMessage
     *
     * @throws \Viserio\Component\Contract\Support\Exception\InvalidArgumentException
     *
     * @return mixed
     */
    protected function create(array $config, string $method, string $errorMessage)
    {
        if (isset($this->extensions[$config['name']])) {
            return $this->callCustomCreator($config['name'], $config);
        }

        if (\method_exists($this, $method)) {
            return $this->{$method}($config);
        }

        throw new InvalidArgumentException(\sprintf($errorMessage, $config['name']));
    }

    /**
     * Get the configuration name.
     *
     * @return string
     */
    abstract protected static function getConfigName(): string;
}
