<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Uri\Filter;

use Viserio\Component\Http\Uri\Traits\PortValidateTrait;

class Port
{
    use PortValidateTrait;

    /**
     * Filter port.
     *
     * @param string $scheme
     * @param mixed  $port
     *
     * @return null|int
     */
    public function filter(string $scheme, $port = null): ?int
    {
        $port = $this->validatePort($port);

        return $this->isNonStandardPort($scheme, $port) ? $port : null;
    }
}
