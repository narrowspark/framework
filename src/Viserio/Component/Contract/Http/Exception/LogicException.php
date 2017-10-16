<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Http\Exception;

use LogicException as BaseLogicException;

class LogicException extends BaseLogicException implements Exception
{
}
