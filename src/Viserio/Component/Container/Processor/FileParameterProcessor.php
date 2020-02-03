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

class FileParameterProcessor extends AbstractParameterProcessor
{
    /**
     * {@inheritdoc}
     */
    public static function getProvidedTypes(): array
    {
        return [
            'file' => 'string',
            'require' => 'bool|int|float|string|array',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $parameter)
    {
        [$key, $processor] = $this->getData($parameter);

        if ($processor === 'require' || $processor === 'file') {
            if (! file_exists($key)) {
                throw new RuntimeException(\sprintf('File [%s] not found (resolved from [%s]).', $key, $parameter));
            }

            if ($processor === 'file') {
                return \file_get_contents($key);
            }

            return require $key;
        }

        throw new RuntimeException(\sprintf('Unsupported processor [%s] for [%s] given.', $processor, $parameter));
    }
}
