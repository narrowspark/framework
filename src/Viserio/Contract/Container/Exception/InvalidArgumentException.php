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

namespace Viserio\Contract\Container\Exception;

use InvalidArgumentException as BaseInvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;

class InvalidArgumentException extends BaseInvalidArgumentException implements ContainerExceptionInterface
{
    /**
     * @param string $className
     *
     * @return self
     */
    public static function classMustNotBeAbstract($className): self
    {
        return new self(\sprintf('Unable to create a proxy for an abstract class "%s".', $className));
    }

    /**
     * @param string $className
     *
     * @return self
     */
    public static function classMustNotBeFinal($className): self
    {
        return new self(\sprintf('Unable to create a proxy for a final class "%s".', $className));
    }
}
