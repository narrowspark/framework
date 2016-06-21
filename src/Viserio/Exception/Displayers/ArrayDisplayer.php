<?php
namespace Viserio\Exception\Adapter;

use Exception;
use OutOfBoundsException;
use Narrowspark\HttpStatus\HttpStatus;
use Viserio\Contracts\Exception\Adapter;

class ArrayDisplayer implements Adapter
{
    /**
     * Display the given exception to the user.
     *
     * @param \Exception $exception
     * @param int        $code
     *
     * @return array
     */
    public function display(Exception $exception, int $code): array
    {
        try {
            $message = HttpStatus::getReasonPhrase($code);
        } catch (OutOfBoundsException $narrowsparkExc) {
            $message =  $exception->getMessage();;
        }

        return ['success' => false, 'code' => $message['code'], 'msg' => $message['extra']];
    }
}
