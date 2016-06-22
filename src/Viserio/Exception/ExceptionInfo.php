<?php
namespace Viserio\Exception;

use InvalidArgumentException;
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
            $info = ['id' => $id, 'code' => $code, 'name' => HttpStatus::getReasonPhrase($code), 'detail' => HttpStatus::getReasonMessage($code)];
        } catch (OutOfBoundsException $error) {
            $info = ['id' => $id, 'code' => 500, 'name' => HttpStatus::getReasonPhrase(500), 'detail' => HttpStatus::getReasonMessage(500)];
        } catch (InvalidArgumentException $error) {
            $info = ['id' => $id, 'code' => 500, 'name' => HttpStatus::getReasonPhrase(500), 'detail' => HttpStatus::getReasonMessage(500)];
        }

        $info['summary'] = 'Houston, We Have A Problem.';

        return $info;
    }
}
