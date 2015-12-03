<?php
namespace Viserio\Contracts\Translator;

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
