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
     *
     * @return string
     */
    public function getTimeType(): string;
}
