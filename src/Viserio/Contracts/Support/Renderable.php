<?php
namespace Viserio\Contracts\Support;

/**
 * Renderable.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
interface Renderable
{
    /**
     * Get the evaluated contents of the object.
     *
     * @return string
     */
    public function render();
}
