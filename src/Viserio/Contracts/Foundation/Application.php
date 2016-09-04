<?php
declare(strict_types=1);
namespace Viserio\Contracts\Foundation;

interface Application
{
    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function getVersion(): string;
}
