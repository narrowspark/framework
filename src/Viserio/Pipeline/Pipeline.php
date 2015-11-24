<?php
namespace Viserio\Pipeline;

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

use Interop\Container\ContainerInterface as ContainerInteropInterface;
use Viserio\Contracts\Pipeline\Pipeline as PipelineContract;

/**
 * Pipeline.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10-dev
 */
class Pipeline implements PipelineContract
{
    /**
     * The container implementation.
     *
     * @var \Interop\Container\ContainerInterface
     */
    protected $container;

    /**
     * Create a new class instance.
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInteropInterface $container)
    {
        $this->container = $container;
    }
}
