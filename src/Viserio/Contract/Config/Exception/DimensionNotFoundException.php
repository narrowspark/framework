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

namespace Viserio\Contract\Config\Exception;

use OutOfBoundsException;
use Throwable;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\Config\RequiresComponentConfigId as RequiresComponentConfigIdContract;
use Viserio\Contract\Config\RequiresConfigId as RequiresConfigIdContract;

class DimensionNotFoundException extends OutOfBoundsException implements Exception
{
    /**
     * Create a new DimensionNotFoundException instance.
     *
     * @param string         $class
     * @param string         $currentDimension Current configuration key
     * @param null|string    $id
     * @param int            $code
     * @param null|Throwable $previous
     */
    public function __construct(
        string $class,
        ?string $currentDimension,
        ?string $id,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $position = [];
        $interfaces = \class_implements($class);
        $dimensions = \array_key_exists(RequiresComponentConfigContract::class, $interfaces) ? $class::getDimensions() : [];

        $hasId = \array_key_exists(RequiresConfigIdContract::class, $interfaces) || \array_key_exists(RequiresComponentConfigIdContract::class, $interfaces);

        if ($hasId && $id !== null) {
            $dimensions[] = $id;
        }

        foreach ($dimensions as $dimension) {
            $position[] = $dimension;

            if ($dimension === $currentDimension) {
                break;
            }
        }

        $message = 'No dimension configuration was set or found for [%s] in class [%s].';

        if ($hasId && $id === null && \count($dimensions) === \count($position)) {
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
