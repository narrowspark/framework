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

namespace Viserio\Bridge\Dotenv;

use Closure;
use Dotenv\Environment\Adapter\EnvConstAdapter;
use Dotenv\Environment\Adapter\PutenvAdapter;
use Dotenv\Environment\Adapter\ServerConstAdapter;
use Dotenv\Environment\DotenvFactory;
use Dotenv\Environment\FactoryInterface;
use Dotenv\Environment\VariablesInterface;
use Error;
use PhpOption\Option;

final class Env
{
    /**
     * If the putenv adapter is enabled.
     *
     * @var bool
     */
    private static $putenv = true;

    /**
     * The environment factory instance.
     *
     * @var null|\Dotenv\Environment\FactoryInterface
     */
    private static $factory;

    /**
     * The environment variables instance.
     *
     * @var null|\Dotenv\Environment\VariablesInterface
     */
    private static $variables;

    /**
     * @throws \Error
     */
    public function __construct()
    {
        throw new Error('Class ' . \get_class($this) . ' is static and cannot be instantiated.');
    }

    /**
     * Get the environment factory instance.
     *
     * @return \Dotenv\Environment\FactoryInterface
     */
    public static function getFactory(): FactoryInterface
    {
        if (static::$factory === null) {
            $adapters = \array_merge(
                [new EnvConstAdapter(), new ServerConstAdapter()],
                static::$putenv ? [new PutenvAdapter()] : []
            );

            static::$factory = new DotenvFactory($adapters);
        }

        return static::$factory;
    }

    /**
     * Get the environment variables instance.
     *
     * @return \Dotenv\Environment\VariablesInterface
     */
    public static function getVariables(): VariablesInterface
    {
        if (static::$variables === null) {
            static::$variables = static::getFactory()->createImmutable();
        }

        return static::$variables;
    }

    /**
     * Enable the putenv adapter.
     *
     * @var bool
     */
    public static function enablePutenv(): void
    {
        static::$putenv = true;
        static::$factory = null;
        static::$variables = null;
    }

    /**
     * Disable the putenv adapter.
     *
     * @var bool
     */
    public static function disablePutenv(): void
    {
        static::$putenv = false;
        static::$factory = null;
        static::$variables = null;
    }

    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        return Option::fromValue(static::getVariables()->get($key))
            ->map(static function ($value) {
                if (\preg_match('/base64:|\'base64:|"base64:/', $value) === 1) {
                    return \base64_decode(\substr($value, 7), true);
                }

                if (\is_numeric($value)) {
                    return $value + 0;
                }

                if (\strlen($value) > 1
                && \substr($value, 0, \strlen('"')) === '"'
                && \substr($value, -\strlen('"')) === '"') {
                    return \substr($value, 1, -1);
                }

                switch (\strtolower($value)) {
                    case 'true':
                    case '(true)':
                    case 'yes':
                    case '(yes)':
                    case 'on)':
                        return true;
                    case 'false':
                    case '(false)':
                    case 'no':
                    case '(no)':
                    case 'off':
                        return false;
                    case 'empty':
                    case '(empty)':
                        return '';
                    case 'null':
                    case '(null)':
                        return;
                }

                if (\preg_match('/\A([\'"])(.*)\1\z/', $value, $matches) === 1) {
                    return $matches[2];
                }

                return $value;
            })
            ->getOrCall(static function () use ($default) {
                return $default instanceof Closure ? $default() : $default;
            });
    }
}
