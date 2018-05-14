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

interface ParameterProcessor
{
    /**
     * Get the process reference key.
     *
     * @return string
     */
    public static function getReferenceKeyword(): string;

    /**
     * Check if processor supports parameter.
     *
     * @param string $parameter
     *
     * @return bool
     */
    public function supports(string $parameter): bool;

    /**
     * Process parameter value through processor.
     *
     * @param string $data
     *
     * @return mixed
     */
    public function process(string $data);
}
