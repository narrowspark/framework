<?php
namespace Viserio\Contracts\Console;

interface Application
{
    /**
     * Add a command to the console.
     *
     * @param string                $expression Defines the arguments and options of the command.
     * @param callable|string|array $callable   Called when the command is called.
     *                                          When using a container, this can be a "pseudo-callable"
     *                                          i.e. the name of the container entry to invoke.
     *
     * @return SymfonyCommand|null
     */
    public function command($expression, $callable);
}
