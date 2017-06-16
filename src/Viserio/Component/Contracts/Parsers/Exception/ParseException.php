<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Parsers\Exception;

use ErrorException;

class ParseException extends ErrorException
{
    public function __construct(array $error)
    {
        $message  = (string) $error['message'];
        $code     = $error['code'] ?? 0;
        $severity = $error['type'] ?? 1;
        $filename = $error['file'] ?? __FILE__;
        $lineno   = $error['line'] ?? __LINE__;
        $previous = $error['exception'] ?? null;

        parent::__construct($message, $code, $severity, $filename, $lineno, $previous);
    }
}
