<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\OptionsResolver\Exceptions;

use OutOfBoundsException;
use Throwable;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfigId as RequiresComponentConfigIdContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfigId as RequiresConfigIdContract;

class OptionNotFoundException extends OutOfBoundsException
{
    /**
     * @param \Viserio\Component\Contracts\OptionsResolver\RequiresConfig $factory
     * @param mixed                                                       $currentDimension Current configuration key
     * @param string|null                                                 $configId
     * @param int                                                         $code
     * @param null|\Throwable                                             $previous
     */
    public function __construct(
        RequiresConfigContract $factory,
        $currentDimension,
        ?string $configId,
        int $code = 0,
        Throwable $previous = null
    ) {
        $position   = [];
        $dimensions = $factory instanceof RequiresComponentConfigContract ? $factory->getDimensions() : [];

        if ($factory instanceof RequiresConfigIdContract || $factory instanceof RequiresComponentConfigIdContract) {
            $dimensions[] = $configId;
        }

        foreach ($dimensions as $dimension) {
            $position[] = $dimension;

            if ($dimension === $currentDimension) {
                break;
            }
        }

        if (($factory instanceof RequiresConfigIdContract || $factory instanceof RequiresComponentConfigIdContract) &&
            $configId === null &&
            count($dimensions) === count($position)
        ) {
            $message = sprintf('The configuration "%s" needs a config id in class "'. get_class($factory) .'".', rtrim(implode('.', $position), '.'));
        } else {
            $message = sprintf('No options set for configuration "%s" in class "'. get_class($factory) .'".', rtrim(implode('.', $position), '.'));
        }

        parent::__construct(
            $message,
            $code,
            $previous
        );
    }
}
