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

namespace Viserio\Bridge\Dotenv\Processor;

use Viserio\Bridge\Dotenv\Env;
use Viserio\Component\Container\Processor\AbstractParameterProcessor;

class EnvParameterProcessor extends AbstractParameterProcessor
{
    /**
     * {@inheritdoc}
     */
    public static function getProvidedTypes(): array
    {
        return [
            'env' => 'bool|int|float|string|array',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $parameter)
    {
        [$key,, $search] = $this->getData($parameter);

        return \str_replace($search, Env::get($key, $parameter), $parameter);
    }
}
