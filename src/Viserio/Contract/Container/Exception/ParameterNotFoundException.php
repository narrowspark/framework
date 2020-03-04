<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Contract\Container\Exception;

use Exception;

class ParameterNotFoundException extends NotFoundException
{
    /**
     * Create a new ParameterNotFoundException instance.
     */
    public function __construct(
        string $id,
        ?string $sourceId = null,
        ?string $sourceKey = null,
        ?Exception $previous = null,
        array $alternatives = [],
        ?string $nonNestedAlternative = null,
        ?string $message = null
    ) {
        if ($sourceId !== null) {
            $message = \sprintf('The service [%s] has a dependency on a non-existent parameter [%s].', $sourceId, $id);
        } elseif ($sourceKey !== null) {
            $message = \sprintf('The parameter [%s] has a dependency on a non-existent parameter [%s].', $sourceKey, $id);
        } elseif ($message === null) {
            $message = \sprintf('You have requested a non-existent parameter [%s].', $id);
        }

        parent::__construct($id, $sourceId, $previous, $alternatives, $message);

        if ($nonNestedAlternative !== null) {
            $this->message .= ' You cannot access nested array items, do you want to inject [' . $nonNestedAlternative . '] instead?';
        }
    }
}
