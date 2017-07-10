<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Support;

interface Renderable
{
    /**
     * Get the evaluated contents of the object.
     *
     * @return string
     */
    public function render(): string;
}
