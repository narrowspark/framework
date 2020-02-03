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
