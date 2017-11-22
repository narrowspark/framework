<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Contract\Migration\Exception;

use InvalidArgumentException as BaseInvalidArgumentException;

class InvalidArgumentException extends BaseInvalidArgumentException implements Exception
{
}
