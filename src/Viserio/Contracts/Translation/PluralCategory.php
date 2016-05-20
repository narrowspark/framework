<?php
namespace Viserio\Contracts\Translation;

interface PluralCategory
{
    /**
     * Returns category key by count.
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count);
}
