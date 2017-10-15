<?php
declare(strict_types=1);
namespace Viserio\Component\Exception;

use Narrowspark\HttpStatus\Exception\InvalidArgumentException;
use Narrowspark\HttpStatus\Exception\OutOfBoundsException;
use Narrowspark\HttpStatus\HttpStatus;
use Viserio\Component\Contract\Exception\ExceptionInfo as ExceptionInfoContract;

class ExceptionInfo implements ExceptionInfoContract
{
    /**
     * {@inheritdoc}
     */
    public function generate(string $id, int $code): array
    {
        try {
            $info = [
                'id'     => $id,
                'code'   => $code,
                'name'   => HttpStatus::getReasonPhrase($code),
                'detail' => HttpStatus::getReasonMessage($code),
            ];
        } catch (InvalidArgumentException | OutOfBoundsException $error) {
            $info = [
                'id'     => $id,
                'code'   => 500,
                'name'   => HttpStatus::getReasonPhrase(500),
                'detail' => HttpStatus::getReasonMessage(500),
            ];
        }

        $info['summary'] = 'Houston, We Have A Problem.';

        return $info;
    }
}
