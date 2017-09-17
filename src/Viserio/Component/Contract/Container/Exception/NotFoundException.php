<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Container\Exception;

use Exception;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends Exception implements NotFoundExceptionInterface
{
}
