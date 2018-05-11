<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Parser\Exception;

use Exception as BaseException;

class ParseException extends BaseException implements Exception
{
    public function __construct(array $error)
    {
        $message    = (string) $error['message'];
        $code       = $error['code']      ?? 0;
        $this->file = $error['file']      ?? __FILE__;
        $this->line = $error['line']      ?? __LINE__;
        $previous   = $error['exception'] ?? null;

        parent::__construct($message, $code, $previous);
    }
}
