<?php
declare(strict_types=1);
namespace Viserio\Contracts\View;

interface Engine
{
    /**
     * Get the evaluated contents of the view.
     *
     * @param array $fileInfo
     * @param array $data
     *
     * @return string
     */
    public function get(array $fileInfo, array $data = []): string;
}
