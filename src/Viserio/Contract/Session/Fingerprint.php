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

namespace Viserio\Contract\Session;

interface Fingerprint
{
    /**
     * Generate session fingerprint.
     *
     * Fingerprint is additional data (eg. user agent info) to ensure very same
     * client is using session.
     *
     * @return string
     */
    public function generate(): string;
}
