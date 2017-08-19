<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Encryption\Exception;

use Exception as BaseException;

class InvalidSaltException extends BaseException implements Exception
{
}
