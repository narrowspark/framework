<?php
declare(strict_types=1);
namespace Viserio\Contracts\WebProfiler;

interface PanelAware
{
    /**
     * @ToDo
     *
     * @return string
     */
    function getPanel(): string;
}
