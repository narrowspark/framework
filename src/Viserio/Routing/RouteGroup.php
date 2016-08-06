<?php
declare(strict_types=1);
namespace Viserio\Routing;

class RouteGroup
{
    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var \Viserio\Routing\RouteCollection
     */
    protected $collection;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * Constructor.
     *
     * @param string                           $prefix
     * @param callable                         $callback
     * @param \Viserio\Routing\RouteCollection $collection
     */
    public function __construct(string $prefix, callable $callback, RouteCollection $collection)
    {
        $this->callback   = $callback;
        $this->collection = $collection;
        $this->prefix     = sprintf('/%s', ltrim($prefix, '/'));
    }

    /**
     * Process the group and ensure routes are added to the collection.
     */
    public function __invoke()
    {
        call_user_func_array($this->callback, [$this]);
    }
}
