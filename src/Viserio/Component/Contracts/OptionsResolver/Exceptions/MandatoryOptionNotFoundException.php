<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\OptionsResolver\Exceptions;

use Throwable;
use OutOfBoundsException;

class MandatoryOptionNotFoundException extends OutOfBoundsException
{
    /**
     * Create a new MandatoryOptionNotFound exception.
     *
     * @param iterable        $dimensions
     * @param string          $option
     * @param mixed           $code
     * @param null|\Throwable $previous
     * @param null|mixed      $path
     */
    public function __construct(iterable $dimensions, string $option, $code = 0, ?Throwable $previous = null, $path = null)
    {
        $depth = '';

        foreach ($dimensions as $dimension) {
            $depth .= '.' . $dimension;
        }

        parent::__construct(sprintf('Mandatory option "%s" was not set for configuration "%s"', $option, $depth), $code, $previous);
    }
}
