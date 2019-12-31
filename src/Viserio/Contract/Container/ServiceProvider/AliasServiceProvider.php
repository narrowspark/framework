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

namespace Viserio\Contract\Container\ServiceProvider;

interface AliasServiceProvider
{
    /**
     * Returns a list of all alias container entries registered by this service provider.
     *
     * - the key is the container alias name.
     * - the value is the original container entry name.
     *
     * @example [
     *      'alias' => 'original name',
     *      'alias' => ['original name', true], the second value in the array is the value of the Alias setPublic function
     * ]
     *
     * @return array<string, array<string, bool>|string>
     */
    public function getAlias(): array;
}
