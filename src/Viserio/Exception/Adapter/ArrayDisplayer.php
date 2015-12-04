<?php
namespace Viserio\Exception\Adapter;

use Viserio\Contracts\Exception\Adapter;
use Viserio\Exception\Traits\ErrorHandlingTrait;

class ArrayDisplayer implements Adapter
{
    use ErrorHandlingTrait;

    /**
     * Display the given exception to the user.
     *
     * @param \Exception $exception
     * @param int        $code
     *
     * @return array
     */
    public function display(\Exception $exception, $code)
    {
        $message = $this->message($code, $exception->getMessage());

        return ['success' => false, 'code' => $message['code'], 'msg' => $message['extra']];
    }
}
