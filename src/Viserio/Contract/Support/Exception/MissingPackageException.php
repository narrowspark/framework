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

namespace Viserio\Contract\Support\Exception;

use RuntimeException as BaseRuntimeException;

class MissingPackageException extends BaseRuntimeException implements Exception
{
    /**
     * {@inheritdoc}
     *
     * @param array  $missingPackages
     * @param string $className
     * @param string $message
     */
    public function __construct(array $missingPackages, string $className, string $message = null)
    {
        parent::__construct(\sprintf(
            "Found Missing package%s, to use the [%s]%s, run:\n\ncomposer require %s",
            \count($missingPackages) > 1 ? 's' : '',
            $className,
            $message ?? '',
            \implode(' ', $missingPackages)
        ));
    }
}
