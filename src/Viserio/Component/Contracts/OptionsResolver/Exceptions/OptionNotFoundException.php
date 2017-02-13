<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\OptionsResolver\Exceptions;

use OutOfBoundsException;
use Throwable;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfigId as RequiresConfigIdContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfigId as RequiresComponentConfigIdContract;

class OptionNotFoundException extends OutOfBoundsException
{
    /**
     * @param \Viserio\Component\Contracts\OptionsResolver\RequiresConfigId $factory
     * @param mixed                                                         $currentDimension Current configuration key
     * @param string|null                                                   $configId
     * @param mixed                                                         $code
     * @param null|\Throwable                                               $previous
     * @param null|mixed                                                    $path
     */
    public function __construct(
        RequiresConfigContract $factory,
        $currentDimension,
        ?string $configId,
        $code = 0,
        Throwable $previous = null,
        $path = null
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
            $message = sprintf('The configuration "%s" needs a config id.', rtrim(implode('.', $position), '.'));
        } else {
            $message = sprintf('No options set for configuration "%s".', rtrim(implode('.', $position), '.'));
        }

        parent::__construct(
            $message,
            $code,
            $previous
        );
    }
}
