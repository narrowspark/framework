<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Cron\Exception;

use LogicException as BaseLogicException;

class LogicException extends BaseLogicException implements Exception
{
}
