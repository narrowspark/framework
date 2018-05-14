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

namespace Viserio\Component\Config\ParameterProcessor;

use Viserio\Bridge\Dotenv\Env;

class EnvParameterProcessor extends AbstractParameterProcessor
{
    /**
     * {@inheritdoc}
     */
    public static function getReferenceKeyword(): string
    {
        return 'env';
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $data)
    {
        $parameterKey = $this->parseParameter($data);

        $value = Env::get($parameterKey, $parameterKey);

        if (! \is_string($value)) {
            return $value;
        }

        return $this->replaceData($data, $parameterKey, $value);
    }
}
