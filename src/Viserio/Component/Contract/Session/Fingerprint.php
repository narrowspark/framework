<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Session;

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
