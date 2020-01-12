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

namespace Viserio\Component\Config\Processor;

use Viserio\Contract\Config\Exception\RuntimeException;

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

        if ($processor === 'query_string') {
            $queryString = \parse_url($key, \PHP_URL_QUERY) ?: $key;

            \parse_str($queryString, $result);

            return $result;
        }

        throw new RuntimeException(\sprintf('Unsupported processor [%s] for [%s] given.', $processor, $parameter));
    }
}
