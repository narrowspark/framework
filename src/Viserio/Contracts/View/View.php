<?php
namespace Viserio\Contracts\View;

use Viserio\Contracts\Support\Renderable;

/**
 * View.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
interface View extends Renderable
{
    /**
     * Get the name of the view.
     *
     * @return string
     */
    public function name();

    /**
     * Add a piece of data to the view.
     *
     * @param string|array $key
     * @param mixed        $value
     *
     * @return $this
     */
    public function with($key, $value = null);
}
