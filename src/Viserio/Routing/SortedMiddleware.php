<?php
declare(strict_types=1);
namespace Viserio\Routing;

class SortedMiddleware
{
    /**
     * All middlewares.
     *
     * @var array
     */
    protected $middlewares = [];

    /**
     * Create a new Sorted Middleware container.
     *
     * @param array $priorityMap
     * @param array $middlewares
     */
    public function __construct(array $priorityMap, array $middlewares)
    {
        $this->middlewares = $this->doSortMiddleware($priorityMap, $middlewares);
    }

    /**
     * Get all sorted middlewares.
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->middlewares;
    }

    /**
     * Sort the middlewares by the given priority map.
     *
     * Each call to this method makes one discrete middleware movement if necessary.
     *
     * @param array $priorityMap
     * @param array $middlewares
     *
     * @return array
     */
    protected function doSortMiddleware(array $priorityMap, array $middlewares): array
    {
        $lastIndex = 0;

        foreach ($middlewares as $index => $middleware) {
            if (in_array($middleware, $priorityMap)) {
                $priorityIndex = array_search($middleware, $priorityMap);

                // This middleware is in the priority map. If we have encountered another middleware
                // that was also in the priority map and was at a lower priority than the current
                // middleware, we will move this middleware to be above the previous encounter.
                if (isset($lastPriorityIndex) && $priorityIndex < $lastPriorityIndex) {
                    return $this->doSortMiddleware(
                        $priorityMap,
                        array_values(
                            $this->moveMiddleware($middlewares, $index, $lastIndex)
                        )
                    );
                }

                // This middleware is in the priority map; but, this is the first middleware we have
                // encountered from the map thus far. We'll save its current index plus its index
                // from the priority map so we can compare against them on the next iterations.
                $lastIndex = $index;
                $lastPriorityIndex = $priorityIndex;
            }
        }

        return array_values(array_unique($middlewares, SORT_REGULAR));
    }

    /**
     * Splice a middleware into a new position and remove the old entry.
     *
     * @param array $middlewares
     * @param int   $from
     * @param int   $to
     *
     * @return array
     */
    protected function moveMiddleware(array $middlewares, int $from, int $to): array
    {
        array_splice($middlewares, $to, 0, $middlewares[$from]);
        unset($middlewares[$from + 1]);

        return $middlewares;
    }
}
