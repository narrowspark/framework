<?php
namespace Viserio\Http\Uri\Filter;

use Viserio\Http\Uri\Traits\HostValidateTrait;

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
