<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Container\Exception;

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
