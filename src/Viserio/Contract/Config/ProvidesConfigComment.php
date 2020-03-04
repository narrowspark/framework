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

namespace Viserio\Contract\Config;

interface ProvidesConfigComment
{
    /**
     * Returns a list of config comments based on the config key.
     *
     * @example [
     *      'foo' => 'comment',
     *      'bar' => [
     *          'baz' => 'comment'
     *      ]
     * ]
     *
     * @return array list with config comments, can be nested
     */
    public static function getConfigComments(): iterable;
}
