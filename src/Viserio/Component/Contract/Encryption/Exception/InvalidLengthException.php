<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Encryption\Exception;

use LengthException;

class InvalidLengthException extends LengthException implements Exception
{
}
