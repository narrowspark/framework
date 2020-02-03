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

class JsonParameterProcessor extends AbstractParameterProcessor
{
    /**
     * {@inheritdoc}
     */
    public static function getProvidedTypes(): array
    {
        return [
            'json' => 'array',
            'json_decode' => 'array',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $parameter)
    {
        [$key] = $this->getData($parameter);

        $json = \json_decode($key, true, \JSON_THROW_ON_ERROR);

        if (\json_last_error() !== \JSON_ERROR_NONE) {
            throw new RuntimeException(\sprintf('Invalid JSON in parameter [%s]: %s.', $parameter, \json_last_error_msg()));
        }

        if ($json !== null && ! \is_array($json)) {
            throw new RuntimeException(\sprintf('Invalid JSON env var [%s]: array or null expected, [%s] given.', $json, \gettype($json)));
        }

        return $json;
    }
}
