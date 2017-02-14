<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\OptionsResolver\Exceptions;

use Throwable;
use UnexpectedValueException as PhpUnexpectedValueException;

class UnexpectedValueException extends PhpUnexpectedValueException
{
    /**
     * Create a new UnexpectedValue exception.
     *
     * @param iterable        $dimensions
     * @param mixed           $currentDimension Current configuration key
     * @param int             $code
     * @param null|\Throwable $previous
     * @param null|mixed      $path
     */
    public function __construct(
        iterable $dimensions,
        $currentDimension = null,
        int $code = 0,
        Throwable $previous = null,
        $path = null
    ) {
        $position = [];

        foreach ($dimensions as $dimension) {
            if ($dimension === $currentDimension) {
                break;
            }

            $position[] = $dimension;
        }

        parent::__construct(
            sprintf(
                'Configuration must either be of type "array" or implement "\ArrayAccess". ' .
                'Configuration position is "%s".',
                rtrim(implode('.', $position), '.')
            ),
            $code,
            $previous
        );
    }
}
