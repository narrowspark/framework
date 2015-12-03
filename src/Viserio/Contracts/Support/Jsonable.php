<?php
namespace Viserio\Contracts\Support;

/**
 * Jsonable.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
interface Jsonable
{
    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson($options = 0);
}
