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

namespace Viserio\Contract\OptionsResolver;

interface DeprecatedOptions
{
    /**
     * Deprecate options that will be no more used in the next version.
     * Key should be available in getMandatoryOptions or getDefaultOptions.
     *
     * The deprecation message supports a sprintf replacer for the key.
     *
     * @return array
     */
    public static function getDeprecatedOptions(): array;
}
