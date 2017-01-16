<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Uri\Filter;

use Viserio\Component\Http\Uri\Traits\HostValidateTrait;

class Host
{
    use HostValidateTrait;

    public function filter(string $host): string
    {
        $host = $this->validateHost($host);

        if ($this->isIdn) {
            $host = idn_to_utf8($host);
        }

        return $this->lower($host);
    }
}
