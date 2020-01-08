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
use Viserio\Contract\Config\Exception\RuntimeException;

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
    public static function supports(string $parameter): bool
    {
        $result = parent::supports($parameter);

        if ($result && ! class_exists(Env::class)) {
            throw new RuntimeException('@todo create a package exception.');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $data)
    {
        $parameterKey = self::parseParameter($data);

        $value = Env::get($parameterKey, $parameterKey);

        if (! \is_string($value)) {
            return $value;
        }

        return self::replaceData($data, $parameterKey, $value);
    }
}
