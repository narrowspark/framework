<?php
declare(strict_types=1);
namespace Viserio\Component\Discovery\Configurator;

function getcwd()
{
    return \sys_get_temp_dir();
}
