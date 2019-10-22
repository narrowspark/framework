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

namespace Viserio\Contract\Http\Exception;

class AccessDeniedException extends RuntimeException
{
    /**
     * Create a new AccessDeniedException instance.
     *
     * @param string $path The path to the accessed file
     */
    public function __construct($path)
    {
        parent::__construct(\sprintf('The file %s could not be accessed', $path));
    }
}
