<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Traits;

trait NormalizeIntegerValueTrait
{
    /**
     * Normalize integer.
     *
     * @param int|float $inter
     *
     * @return int
     */
    public function normalizeInteger($inter)
    {
        $inter = trim((string) $inter);
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
