<?php
namespace Viserio\Contracts\Exception;

/**
 * Adapter.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
interface Adapter
{
    /**
     * Display the given exception to the user.
     *
     * @param \Exception $exception
     * @param int        $code
     */
    public function display(\Exception $exception, $code);
}
