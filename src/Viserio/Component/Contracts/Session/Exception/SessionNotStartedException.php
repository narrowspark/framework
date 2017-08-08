<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Session\Exception;

use RuntimeException;

class SessionNotStartedException extends RuntimeException implements Exception
{
}
