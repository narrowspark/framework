<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Filesystem\Exception;

use OutOfBoundsException as BaseOutOfBoundsException;

class OutOfBoundsException extends BaseOutOfBoundsException implements Exception
{
}
