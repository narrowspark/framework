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

class PhpTypeParameterProcessor extends AbstractParameterProcessor
{
    /**
     * {@inheritdoc}
     */
    public static function getProvidedTypes(): array
    {
        return [
            'bool' => 'bool',
            'float' => 'float',
            'int' => 'int',
            'string' => 'string',
            'trim' => 'string',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $parameter)
    {
        [$key, $processor] = $this->getData($parameter);

        if ($processor === 'string') {
            return (string) $key;
        }

        if ($processor === 'bool') {
            return (bool) (\filter_var($key, \FILTER_VALIDATE_BOOLEAN) ?: \filter_var($key, \FILTER_VALIDATE_INT) ?: \filter_var($key, \FILTER_VALIDATE_FLOAT));
        }

        if ($processor === 'int') {
            if (false === $key = \filter_var($key, \FILTER_VALIDATE_INT) ?: \filter_var($key, \FILTER_VALIDATE_FLOAT)) {
                throw new RuntimeException(\sprintf('Non-numeric env var "%s" cannot be cast to int.', $parameter));
            }

            return (int) $key;
        }

        if ($processor === 'float') {
            if (false === $key = \filter_var($key, \FILTER_VALIDATE_FLOAT)) {
                throw new RuntimeException(\sprintf('Non-numeric env var "%s" cannot be cast to float.', $parameter));
            }

            return (float) $key;
        }

        if ($processor === 'base64' || $processor === 'base64_decode') {
            return \base64_decode(\strtr($key, '-_', '+/'), true);
        }

        if ($processor === 'trim') {
            return \trim($key);
        }

        throw new RuntimeException(\sprintf('Unsupported processor [%s] for [%s] given.', $processor, $parameter));
    }
}
