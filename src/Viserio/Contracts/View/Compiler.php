<?php
namespace Viserio\Contracts\View;

/**
 * Compiler.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
interface Compiler
{
    /**
     * Get the path to the compiled version of a view.
     *
     * @param string $path
     *
     * @return string
     */
    public function getCompiledPath($path);

    /**
     * Determine if the given view is expired.
     *
     * @param string $path
     *
     * @return bool
     */
    public function isExpired($path);

    /**
     * Compile the view at the given path.
     *
     * @param string $path
     */
    public function compile($path);
}
