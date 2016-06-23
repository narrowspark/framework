<?php
namespace Viserio\Exception;

use InvalidArgumentException;
use Narrowspark\HttpStatus\HttpStatus;

class ExceptionInfo
{
    /**
     * Get the exception information.
     *
     * @param string     $id
     * @param int        $code
     *
     * @return array
     */
    public function generate(string $id, int $code): array
    {
        try {
            $info = [
                'id' => $id,
                'code' => $code,
                'name' => HttpStatus::getReasonPhrase($code),
                'detail' => HttpStatus::getReasonMessage($code)
            ];
        } catch (InvalidArgumentException $error) {
            $info = [
                'id' => $id,
                'code' => 500,
                'name' => HttpStatus::getReasonPhrase(500),
                'detail' => HttpStatus::getReasonMessage(500)
            ];
        }

        $info['summary'] = 'Houston, We Have A Problem.';

        return $info;
    }
}
