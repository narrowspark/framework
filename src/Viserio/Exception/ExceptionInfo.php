<?php
namespace Viserio\Exception;

use Throwable;
use Narrowspark\HttpStatus\HttpStatus;
use OutOfBoundsException;

class ExceptionInfo
{
    /**
     * Get the exception information.
     *
     * @param \Throwable $exception
     * @param string     $id
     * @param int        $code
     *
     * @return array
     */
    public function generate(Throwable $exception, string $id, int $code): array
    {
        try {
            $info = ['id' => $id, 'code' => $code, 'name' => Narrowspark::getReasonPhrase($code), 'detail' => ''];
        } catch (OutOfBoundsException $error) {
            $info = ['id' => $id, 'code' => 500, 'name' => Narrowspark::getReasonPhrase(500), 'detail' => ''];
        }

        $info['summary'] = 'Houston, We Have A Problem.';

        return $info;
    }
}
