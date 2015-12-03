<?php
namespace Viserio\Contracts\Translator;

/**
 * PluralCategory.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6
 */
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
