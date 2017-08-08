<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Routing\Exception;

use InvalidArgumentException;

class RouteNotFoundException extends InvalidArgumentException implements Exception
{
}
