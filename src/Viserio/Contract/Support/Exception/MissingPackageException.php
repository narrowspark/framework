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

namespace Viserio\Contract\Support\Exception;

use RuntimeException as BaseRuntimeException;

class MissingPackageException extends BaseRuntimeException implements Exception
{
    /**
     * {@inheritdoc}
     *
     * @param string $message
     */
    public function __construct(array $missingPackages, string $className, ?string $message = null)
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
