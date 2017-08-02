<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Console\Exception;

use LogicException as BaseLogicException;

class LogicException extends BaseLogicException implements Exception
{
}
