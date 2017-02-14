<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\OptionsResolver\Exceptions;

use OutOfBoundsException;
use Throwable;

class MandatoryOptionNotFoundException extends OutOfBoundsException
{
    /**
     * Create a new MandatoryOptionNotFound exception.
     *
     * @param iterable        $dimensions
     * @param string          $option
     * @param int             $code
     * @param null|\Throwable $previous
     * @param null|mixed      $path
     */
    public function __construct(
        iterable $dimensions,
        string $option,
        int $code = 0,
        Throwable $previous = null,
        $path = null
    ) {
        $depth = '';

        foreach ($dimensions as $dimension) {
            if ($depth !== '') {
                $depth .= '.' . $dimension;
            } else {
                $depth .= $dimension;
            }
        }

        parent::__construct(
            sprintf(
                'Mandatory option "%s" was not set for configuration "%s".',
                $option,
                $depth
            ),
            $code,
            $previous
        );
    }
}
