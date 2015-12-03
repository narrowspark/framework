<?php
namespace Viserio\Contracts\Routing;

interface RouteStrategy
{
    /**
     * Types of route strategies.
     */
    const REQUEST_RESPONSE_STRATEGY = 0;
    const RESTFUL_STRATEGY = 1;
    const URI_STRATEGY = 2;

    /**
     * Tells the implementor which strategy to use, this should override any higher
     * level setting of strategies, such as on specific routes.
     *
     * @param int $strategy
     */
    public function setStrategy($strategy);

    /**
     * Gets global strategy.
     *
     * @return int
     */
    public function getStrategy();
}
