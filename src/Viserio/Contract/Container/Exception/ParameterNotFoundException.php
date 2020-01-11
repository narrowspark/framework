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

namespace Viserio\Contract\Container\Exception;

use Exception;

class ParameterNotFoundException extends NotFoundException
{
    /**
     * Create a new ParameterNotFoundException instance.
     *
     * @param string         $id
     * @param null|string    $sourceId
     * @param null|string    $sourceKey
     * @param null|Exception $previous
     * @param array          $alternatives
     * @param null|string    $nonNestedAlternative
     * @param null|string    $message
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
