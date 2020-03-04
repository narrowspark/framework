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

class Base64ParameterProcessor extends AbstractParameterProcessor
{
    /**
     * {@inheritdoc}
     */
    public static function getProvidedTypes(): array
    {
        return [
            'base64' => 'string',
            'base64_decode' => 'string',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $parameter)
    {
        [$key,] = $this->getData($parameter);

        $value = \base64_decode(\strtr($key, '-_', '+/'), true);

        if ($value === false) {
            throw new RuntimeException(\sprintf('Base64 decoding of [%s] failed, on given parameter [%s].', $key, $parameter));
        }

        return $value;
    }
}
