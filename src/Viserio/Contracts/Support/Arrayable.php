<?php
namespace Viserio\Contracts\Support;

/**
 * ArrayableInterface.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
interface Arrayable
{
    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray();
}
