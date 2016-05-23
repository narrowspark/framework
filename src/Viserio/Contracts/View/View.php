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
    public function getName(): string;

    /**
     * Add a piece of data to the view.
     *
     * @param string|array $key
     * @param mixed        $value
     *
     * @return self
     */
    public function with($key, $value = null): self;

    /**
     * Get the string contents of the view.
     *
     * @param callable|null $callback
     *
     * @return string
     */
    public function render(callable $callback = null): string;
}
