<?php
namespace Viserio\Contracts\Exception;

use Exception;

interface Adapter
{
    /**
     * Display the given exception to the user.
     *
     * @param \Exception $exception
     * @param int        $code
     */
    public function display(Exception $exception, int $code);
}
