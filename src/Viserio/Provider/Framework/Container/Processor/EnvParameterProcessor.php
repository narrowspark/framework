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

namespace Viserio\Provider\Framework\Container\Processor;

use Viserio\Component\Container\Processor\AbstractParameterProcessor;

class EnvParameterProcessor extends AbstractParameterProcessor
{
    /**
     * {@inheritdoc}
     */
    public static function getProvidedTypes(): array
    {
        return [
            'env' => 'string',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $parameter)
    {
        [$key, , $search] = $this->getData($parameter);

        $env = null;

        if (isset($_ENV[$key])) {
            $env = $_ENV[$key];
        } elseif (isset($_SERVER[$key]) && \strpos($key, 'HTTP_') !== 0) {
            $env = $_SERVER[$key];
        } elseif ($env === null && false !== $getEnv = \getenv($key)) {
            $env = $getEnv;
        }

        if (\is_string($env)) {
            return \str_replace($search, $env, $parameter);
        }

        return $env;
    }
}
