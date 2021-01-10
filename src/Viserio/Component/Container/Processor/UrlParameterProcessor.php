<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Container\Processor;

use Viserio\Contract\Container\Exception\RuntimeException;

class UrlParameterProcessor extends AbstractParameterProcessor
{
    /**
     * {@inheritdoc}
     */
    public static function getProvidedTypes(): array
    {
        return [
            'url' => 'array',
            'query_string' => 'array',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $parameter)
    {
        [$key, $processor] = $this->getData($parameter);

        if ($processor === 'url') {
            $parsed = \parse_url($key);

            if ($parsed === false) {
                throw new RuntimeException(\sprintf('Invalid URL in parameter [%s]', $parameter));
            }

            if (! isset($parsed['scheme'], $parsed['host'])) {
                throw new RuntimeException(\sprintf('Invalid URL parameter [%s]: schema and host expected, [%s] given.', $parameter, $key));
            }

            $parsed += [
                'port' => null,
                'user' => null,
                'pass' => null,
                'path' => null,
                'query' => null,
                'fragment' => null,
            ];

            // remove the '/' separator
            if ($parsed['path'] === '/') {
                $parsed['path'] = null;
            } elseif ($parsed['path'] !== null) {
                $parsed['path'] = \substr($parsed['path'], 1);
            }

            return $parsed;
        }

        if ($processor === 'query_string') {
            $parsed = \parse_url($key, \PHP_URL_QUERY);
            /** @var string $queryString */
            $queryString = $parsed !== false ? $parsed : $key;

            \parse_str($queryString, $result);

            return $result;
        }

        throw new RuntimeException(\sprintf('Unsupported processor [%s] for [%s] given.', $processor, $parameter));
    }
}
