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
