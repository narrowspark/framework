<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Routing\Exception;

use InvalidArgumentException;

class RouteNotFoundException extends InvalidArgumentException implements Exception
{
}
