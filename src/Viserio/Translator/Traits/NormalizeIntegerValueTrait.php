<?php
namespace Viserio\Translator\Traits;

trait NormalizeIntegerValueTrait
{
    public function normalizeInteger($inter)
    {
        $inter = (string) $inter;
        $dot   = explode('.', $inter);

        if (isset($dot[1]) && $dot[1] === '0') {
            return $dot[0] + 0;
        }

        if (is_string($inter) && is_numeric($inter)) {
            return $inter + 0;
        }

        return $inter;
    }
}
