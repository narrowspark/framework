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

namespace Viserio\Contract\Container\Dumper;

interface Dumper
{
    /**
     * Characters that might appear in the generated variable name as first character.
     *
     * @var string
     */
    public const FIRST_CHARS = 'abcdefghijklmnopqrstuvwxyz';

    /**
     * Characters that might appear in the generated variable name as any but the first character.
     *
     * @var string
     */
    public const NON_FIRST_CHARS = 'abcdefghijklmnopqrstuvwxyz0123456789_';

    /** @var array */
    public const INTERNAL_TYPES = [
        'int' => true,
        'float' => true,
        'string' => true,
        'bool' => true,
        'resource' => true,
        'object' => true,
        'array' => true,
        'null' => true,
        'callable' => true,
        'iterable' => true,
        'mixed' => true,
    ];

    /**
     * Dumps the service container as a PHP class.
     *
     * @param array $options
     *
     * Available options:
     *
     *  * class:      The class name
     *  * base_class: The base class name
     *  * namespace:  The class namespace
     *  * debug:      To dump a container in debug mode
     *  * as_files:   To split the container in several files
     *  * file:       Is need if as_files option is used, the file path
     *  * build_time:
     *
     * @throws \Viserio\Contract\Container\Exception\CircularDependencyException
     *
     * @return array|string A PHP class representing the service container or an array of PHP files if the "as_files" option is set
     */
    public function dump(array $options = []);
}
