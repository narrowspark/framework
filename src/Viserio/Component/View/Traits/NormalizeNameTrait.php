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

namespace Viserio\Component\View\Traits;

use Viserio\Contract\View\Finder as FinderContract;

trait NormalizeNameTrait
{
    /**
     * Normalize a view name.
     */
    protected function normalizeName(string $name): string
    {
        $delimiter = FinderContract::HINT_PATH_DELIMITER;

        if (\strpos($name, $delimiter) === false) {
            return \str_replace('/', '.', $name);
        }

        [$namespace, $name] = \explode($delimiter, $name);

        return $namespace . $delimiter . \str_replace('/', '.', $name);
    }
}
