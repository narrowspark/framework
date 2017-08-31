<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Filesystem\Exception;

use RuntimeException as BaseRuntimeException;

class FileAccessDeniedException extends BaseRuntimeException implements Exception
{
}
