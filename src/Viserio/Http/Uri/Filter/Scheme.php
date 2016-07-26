<?php
declare(strict_types=1);
namespace Viserio\Http\Uri\Filter;

class Scheme
{
    /**
     * @return string
     */
    public function filter(string $scheme): string
    {
        $scheme = strtolower($scheme);
        $scheme = preg_replace('#:(//)?$#', '', $scheme);

        if (empty($scheme)) {
            return '';
        }

        return $scheme;
    }
}
