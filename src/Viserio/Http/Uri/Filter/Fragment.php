<?php
namespace Viserio\Http\Uri\Filter;

use Viserio\Http\Uri\Traits\TranscoderTrait;

class Fragment
{
    use TranscoderTrait;

    /**
     * Filter a fragment value to ensure it is properly encoded.
     *
     * @param string $fragment
     *
     * @return string
     */
    public function filter(string $fragment): string
    {
        if ($fragment != '' && strpos($fragment, '#') === 0) {
            $fragment = '%23' . substr($fragment, 1);
        }

        return self::encodeQueryFragment($fragment);
    }
}
