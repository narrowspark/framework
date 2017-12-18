<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Exception;


interface ConsoleOutput
{
    public const VERBOSITY_VERBOSE = 64;

    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param string $message The message as an array of lines of a single string
     *
     * @return void
     */
    public function writeln(string $message): void;


    /**
     * Gets the current verbosity of the output.
     *
     * @return int The current level of verbosity (one of the VERBOSITY constants)
     */
    public function getVerbosity(): int;
}
