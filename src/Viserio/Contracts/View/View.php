<?php
namespace Viserio\Contracts\View;

use Viserio\Contracts\Support\Renderable;

interface View extends Renderable
{
    /**
     * Get the name of the view.
     *
     * @return string
     */
    public function getName();

    /**
     * Add a piece of data to the view.
     *
     * @param string|array $key
     * @param mixed        $value
     *
     * @return $this
     */
    public function with($key, $value = null);

    /**
     * Get the string contents of the view.
     *
     * @param callable|null $callback
     *
     * @return string
     */
    public function render(callable $callback = null);
}
