<?php
declare(strict_types=1);
namespace Viserio\Encryption\Providers;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;
use Doctrine\Common\EventManager;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;

class DatabaseProvider implements ServiceProvider
{
}
