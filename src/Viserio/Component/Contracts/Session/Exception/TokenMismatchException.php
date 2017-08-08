<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Session\Exception;

use Exception as BaseException;

class TokenMismatchException extends BaseException implements Exception
{
}
