<?php
namespace Viserio\Exception\Displayers;

use Exception;
use OutOfBoundsException;
use Narrowspark\HttpStatus\HttpStatus;
use Viserio\Contracts\Exception\Displayer as DisplayerContract;

class ArrayDisplayer implements DisplayerContract
{
    /**
    * {@inheritdoc}
     */
    public function display($exception, int $code)
    {
        try {
            $message = HttpStatus::getReasonPhrase($code);
        } catch (OutOfBoundsException $narrowsparkExc) {
            $message =  $exception->getMessage();
        }

        return ['success' => false, 'code' => $message['code'], 'msg' => $message['extra']];
    }
}
