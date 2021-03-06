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

namespace Viserio\Component\Manager\Traits;

use Closure;
use Viserio\Contract\Manager\Exception\InvalidArgumentException;

/** @internal */
trait ManagerTrait
{
    /**
     * The cache of studly-cased words.
     *
     * @var array
     */
    protected static $studlyCache = [];

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
    public static function getMandatoryConfig(): iterable
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
            $config = $adapter[$name];
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
     * @throws \Viserio\Contract\Manager\Exception\InvalidArgumentException
     *
     * @return mixed
     */
    protected function create(array $config, string $method, string $errorMessage)
    {
        $name = $config['name'];

        if (isset($this->extensions[$name])) {
            return $this->callCustomCreator($name, $config);
        }

        if (\method_exists($this, $method)) {
            return $this->{$method}($config);
        }

        throw new InvalidArgumentException(\sprintf($errorMessage, $name));
    }

    /**
     * Convert a value to studly caps case.
     *
     * @param string $value
     *
     * @return string
     */
    protected static function studly(string $value): string
    {
        $key = $value;

        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }

        if (\ctype_upper(\str_replace(['_', '-'], '', $value))) {
            $value = \strtolower($value);
        }

        $value = \ucwords(\str_replace(['-', '_'], ' ', $value));

        return static::$studlyCache[$key] = \str_replace(' ', '', $value);
    }

    /**
     * Get the configuration name.
     *
     * @return string
     */
    abstract protected static function getConfigName(): string;
}
