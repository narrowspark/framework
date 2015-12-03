<?php
namespace Viserio\Contracts\Support;

/**
 * Xmlable.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.8
 */
interface Xmlable
{
    /**
     * Convert the object to its XML representation.
     *
     * @return string
     */
    public function toXml();
}
