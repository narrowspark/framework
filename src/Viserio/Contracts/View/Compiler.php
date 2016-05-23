<?php
namespace Viserio\Contracts\View;

interface Compiler
{
    /**
     * Get the path to the compiled version of a view.
     *
     * @param string $path
     *
     * @return string
     */
    public function getCompiledPath(string $path): string;

    /**
     * Determine if the given view is expired.
     *
     * @param string $path
     *
     * @return bool
     */
    public function isExpired(string $path): bool;

    /**
     * Compile the view at the given path.
     *
     * @param string $path
     */
    public function compile(string $path);
}
