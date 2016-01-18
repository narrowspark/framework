<?php
namespace Viserio\Routing;

use InvalidArgumentException;
use Viserio\Contracts\Routing\CustomStrategy;

trait RouteStrategyTrait
{
    /**
     * @var \Viserio\Contracts\Routing\CustomStrategy|int
     */
    protected $strategy;

    /**
     * Tells the implementor which strategy to use, this should override any higher
     * level setting of strategies, such as on specific routes.
     *
     * @param int|\Viserio\Contracts\Routing\CustomStrategy $strategy
     */
    public function setStrategy($strategy)
    {
        if (is_int($strategy) || $strategy instanceof CustomStrategy) {
            $this->strategy = $strategy;

            return;
        }

        throw new InvalidArgumentException(
            'Provided strategy must be an integer or an instance of [\Viserio\Contracts\Routing\CustomStrategy]'
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
