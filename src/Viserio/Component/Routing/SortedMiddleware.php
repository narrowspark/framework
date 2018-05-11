<?php
declare(strict_types=1);
namespace Viserio\Component\Routing;

class SortedMiddleware
{
    /**
     * All middleware.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * Create a new Sorted Middleware container.
     *
     * @param array $priorityMap
     * @param array $middleware
     */
    public function __construct(array $priorityMap, array $middleware)
    {
        $this->middleware = $this->doSortMiddleware($priorityMap, $middleware);
    }

    /**
     * Get all sorted middleware.
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->middleware;
    }

    /**
     * Sort the middleware by the given priority map.
     *
     * Each call to this method makes one discrete middleware movement if necessary.
     *
     * @param array $priorityMap
     * @param array $middleware
     *
     * @return array
     */
    protected function doSortMiddleware(array $priorityMap, array $middleware): array
    {
        $lastIndex = $lastPriorityIndex = 0;

        foreach ($middleware as $index => $mware) {
            if (\in_array($mware, $priorityMap, true)) {
                $priorityIndex = \array_search($mware, $priorityMap, true);

                // This middleware is in the priority map. If we have encountered another middleware
                // that was also in the priority map and was at a lower priority than the current
                // middleware, we will move this middleware to be above the previous encounter.
                if (isset($lastPriorityIndex) && $priorityIndex < $lastPriorityIndex) {
                    return $this->doSortMiddleware(
                        $priorityMap,
                        \array_values(
                            $this->moveMiddleware($middleware, $index, $lastIndex)
                        )
                    );
                }

                // This middleware is in the priority map; but, this is the first middleware we have
                // encountered from the map thus far. We'll save its current index plus its index
                // from the priority map so we can compare against them on the next iterations.
                $lastIndex         = $index;
                $lastPriorityIndex = $priorityIndex;
            }
        }

        return \array_values(\array_unique($middleware, SORT_REGULAR));
    }

    /**
     * Splice a middleware into a new position and remove the old entry.
     *
     * @param array $middleware
     * @param int   $from
     * @param int   $to
     *
     * @return array
     */
    protected function moveMiddleware(array $middleware, int $from, int $to): array
    {
        \array_splice($middleware, $to, 0, $middleware[$from]);

        unset($middleware[$from + 1]);

        return $middleware;
    }
}
