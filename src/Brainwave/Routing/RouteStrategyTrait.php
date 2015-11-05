<?php
namespace Brainwave\Routing;

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

use Brainwave\Contracts\Routing\CustomStrategy;

/**
 * RouteStrategyTrait.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
trait RouteStrategyTrait
{
    /**
     * @var \Brainwave\Contracts\Routing\CustomStrategy|int
     */
    protected $strategy;

    /**
     * Tells the implementor which strategy to use, this should override any higher
     * level setting of strategies, such as on specific routes.
     *
     * @param int|\Brainwave\Contracts\Routing\CustomStrategy $strategy
     */
    public function setStrategy($strategy)
    {
        if (is_integer($strategy) || $strategy instanceof CustomStrategy) {
            $this->strategy = $strategy;

            return;
        }

        throw new \InvalidArgumentException(
            'Provided strategy must be an integer or an instance of [\Brainwave\Contracts\Routing\CustomStrategy]'
        );
    }

    /**
     * Gets global strategy.
     *
     * @return int
     */
    public function getStrategy()
    {
        return $this->strategy;
    }
}
