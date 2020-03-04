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

namespace Viserio\Contract\Finder\Comparator;

interface DateComparator
{
    /** @var string */
    public const LAST_MODIFIED = 'M';

    /** @var string */
    public const LAST_CHANGED = 'C';

    /** @var string */
    public const LAST_ACCESSED = 'A';

    /**
     * Returns the given time type.
     */
    public function getTimeType(): string;
}
