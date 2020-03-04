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

namespace Viserio\Contract\Http\Exception;

class FileNotFoundException extends FileException
{
    /**
     * Create a new FileNotFoundException instance.
     *
     * @param string $path The path to the file that was not found
     */
    public function __construct($path)
    {
        parent::__construct(\sprintf('The file [%s] does not exist.', $path));
    }
}
