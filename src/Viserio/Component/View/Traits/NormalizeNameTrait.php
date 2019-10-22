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

namespace Viserio\Component\View\Traits;

use Viserio\Contract\View\Finder as FinderContract;

trait NormalizeNameTrait
{
    /**
     * Normalize a view name.
     *
     * @param string $name
     *
     * @return string
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
