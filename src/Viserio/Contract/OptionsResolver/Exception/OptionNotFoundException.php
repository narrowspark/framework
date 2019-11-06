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

namespace Viserio\Contract\OptionsResolver\Exception;

use OutOfBoundsException;
use Throwable;
use Viserio\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\OptionsResolver\RequiresComponentConfigId as RequiresComponentConfigIdContract;
use Viserio\Contract\OptionsResolver\RequiresConfigId as RequiresConfigIdContract;

class OptionNotFoundException extends OutOfBoundsException implements Exception
{
    /**
     * Create a new.
     *
     * @param string         $class
     * @param string         $currentDimension Current configuration key
     * @param null|string    $configId
     * @param int            $code
     * @param null|Throwable $previous
     */
    public function __construct(
        string $class,
        ?string $currentDimension,
        ?string $configId,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $position = [];
        $interfaces = \class_implements($class);
        $dimensions = isset($interfaces[RequiresComponentConfigContract::class]) ? $class::getDimensions() : [];

        $hasConfigIdInterface = (
            isset($interfaces[RequiresConfigIdContract::class])
            || isset($interfaces[RequiresComponentConfigIdContract::class])
        );

        if ($hasConfigIdInterface && $configId !== null) {
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
            \sprintf($message, $this->printArray($position), $class),
            $code,
            $previous
        );
    }

    private function printArray(array $data): string
    {
        $arrayString = '';
        $lastKey = \count($data) - 1;

        foreach ($data as $key => $value) {
            if ($key !== $lastKey) {
                $arrayString .= \sprintf('["%s" => ', $value);
            } else {
                $arrayString .= \sprintf('["%s"]%s', $value, \str_repeat(']', $lastKey));
            }
        }

        return $arrayString;
    }
}
