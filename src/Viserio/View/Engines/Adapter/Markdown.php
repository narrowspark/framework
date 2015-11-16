<?php
namespace Viserio\View\Engines\Adapter;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Viserio\Contracts\View\Engine as EnginesContract;

/**
 * Markdown.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class Markdown implements EnginesContract
{
    /**
     * Get the evaluated contents of the view.
     *
     * @param string $path
     * @param array  $data
     *
     * @return string
     */
    public function get($path, array $data = [])
    {
    }
}
