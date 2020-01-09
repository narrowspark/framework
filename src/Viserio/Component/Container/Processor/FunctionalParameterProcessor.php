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

namespace Viserio\Component\Container\Processor;

use Viserio\Contract\Container\Exception\RuntimeException;

class FunctionalParameterProcessor extends AbstractParameterProcessor
{
    /**
     * {@inheritdoc}
     */
    public static function getProvidedTypes(): array
    {
        return [
            'base64' => 'string',
            'base64_decode' => 'string',
            'csv' => 'array',
            'str_getcsv' => 'array',
            'file' => 'string',
            'json' => 'array',
            'json_decode' => 'array',
            'url' => 'array',
            'query_string' => 'array',
            'trim' => 'string',
            'require' => 'bool|int|float|string|array',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $parameter)
    {
        \preg_match('/\{(' . \implode('|', \array_keys(static::getProvidedTypes())) . ')\:(.*)\}/', $parameter, $matches);

        [, $prefix, $key] = $matches;

        if ($prefix === 'file' || $prefix === 'require') {
            if (! file_exists($key)) {
                throw new RuntimeException(\sprintf('File [%s] not found (resolved from [%s]).', $key, $parameter));
            }

            if ($prefix === 'file') {
                return \file_get_contents($key);
            }

            return require $key;
        }

        if ($prefix === 'base64' || $prefix === 'base64_decode') {
            return \base64_decode(\strtr($key, '-_', '+/'), true);
        }

        if ($prefix === 'json' || $prefix === 'json_decode') {
            $env = \json_decode($key, true, \JSON_THROW_ON_ERROR);

            if (null !== $env && ! \is_array($env)) {
                throw new RuntimeException(\sprintf('Invalid JSON env var [%s]: array or null expected, [%s] given.', $parameter, \gettype($env)));
            }

            return $env;
        }

        if ($prefix === 'url') {
            $parsedEnv = \parse_url($key);

            if ($parsedEnv === false) {
                throw new RuntimeException(\sprintf('Invalid URL in env var [%s]', $parameter));
            }

            if (! isset($parsedEnv['scheme'], $parsedEnv['host'])) {
                throw new RuntimeException(\sprintf('Invalid URL env var [%s]: schema and host expected, [%s] given.', $parameter, $key));
            }

            $parsedEnv += [
                'port' => null,
                'user' => null,
                'pass' => null,
                'path' => null,
                'query' => null,
                'fragment' => null,
            ];

            // remove the '/' separator
            $parsedEnv['path'] = $parsedEnv['path'] === '/' ? null : \substr($parsedEnv['path'], 1);

            return $parsedEnv;
        }

        if ($prefix === 'query_string') {
            $queryString = \parse_url($key, \PHP_URL_QUERY) ?: $key;
            \parse_str($queryString, $result);

            return $result;
        }

        if ($prefix === 'csv' || $prefix === 'str_getcsv') {
            return \str_getcsv($key, ',', '"', \PHP_VERSION_ID >= 70400 ? '' : '\\');
        }

        if ($prefix === 'trim') {
            return \trim($key);
        }

        return $parameter;
    }
}
