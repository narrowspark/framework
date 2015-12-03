<?php
namespace Viserio\Contracts\View;

/**
 * Engine.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
 */
interface Engine
{
    /**
     * Get the evaluated contents of the view.
     *
     * @param string $path
     * @param array  $data
     *
     * @return string
     */
    public function get($path, array $data = []);
}
