<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\OptionsResolver\Exception;

use OutOfBoundsException;
use Throwable;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfigId as RequiresComponentConfigIdContract;
use Viserio\Component\Contract\OptionsResolver\RequiresConfigId as RequiresConfigIdContract;

class OptionNotFoundException extends OutOfBoundsException implements Exception
{
    /**
     * Create a new.
     *
     * @param string          $class
     * @param mixed           $currentDimension Current configuration key
     * @param null|string     $configId
     * @param int             $code
     * @param null|\Throwable $previous
     */
    public function __construct(
        string $class,
        $currentDimension,
        ?string $configId,
        int $code = 0,
        Throwable $previous = null
    ) {
        $position             = [];
        $interfaces           = \class_implements($class);
        $dimensions           = isset($interfaces[RequiresComponentConfigContract::class]) ? $class::getDimensions() : [];
        $hasConfigIdInterface = (
            isset($interfaces[RequiresConfigIdContract::class]) ||
            isset($interfaces[RequiresComponentConfigIdContract::class])
        );

        if ($hasConfigIdInterface) {
            $dimensions[] = $configId;
        }

        foreach ($dimensions as $dimension) {
            $position[] = $dimension;

            if ($dimension === $currentDimension) {
                break;
            }
        }

        $message = 'No options set for configuration [%s] in class [%s].';

        if ($hasConfigIdInterface && $configId === null && \count($dimensions) === \count($position)) {
            $message = 'The configuration [%s] needs a config id in class [%s].';
        }

        parent::__construct(
            \sprintf($message, \rtrim(\implode('.', $position), '.'), $class),
            $code,
            $previous
        );
    }
}
