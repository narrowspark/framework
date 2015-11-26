<?php
namespace Viserio\Container\Definition;

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

use Interop\Container\Definition\AliasDefinitionInterface;

/**
 * Reference.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0-dev
 */
class Reference implements ReferenceInterface
{
    /**
     * @var string
     */
    private $target;
    /**
     * @param string $target
     */
    public function __construct($target)
    {
        $this->target = $target;
    }
    /**
     * Returns the name of the target container entry.
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }
}
