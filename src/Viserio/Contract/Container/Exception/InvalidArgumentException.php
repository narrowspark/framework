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

namespace Viserio\Contract\Container\Exception;

use InvalidArgumentException as BaseInvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;

class InvalidArgumentException extends BaseInvalidArgumentException implements ContainerExceptionInterface
{
    /**
     * @param string $className
     */
    public static function classMustNotBeAbstract($className): self
    {
        return new self(\sprintf('Unable to create a proxy for an abstract class "%s".', $className));
    }

    /**
     * @param string $className
     */
    public static function classMustNotBeFinal($className): self
    {
        return new self(\sprintf('Unable to create a proxy for a final class "%s".', $className));
    }
}
