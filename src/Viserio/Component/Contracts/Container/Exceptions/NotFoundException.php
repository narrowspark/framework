<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Container\Exceptions;

use Exception;
use Interop\Container\Exception\NotFoundException as InteropNotFoundException;

class NotFoundException extends Exception implements InteropNotFoundException
{
}
