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
