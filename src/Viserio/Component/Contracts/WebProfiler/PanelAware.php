<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\WebProfiler;

interface PanelAware
{
    /**
     * @ToDo
     *
     * @return string
     */
    public function getPanel(): string;
}
