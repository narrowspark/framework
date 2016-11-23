<?php
declare(strict_types=1);
namespace Viserio\Routing;

use Closure;
use Viserio\Pipeline\Pipeline as BasePipeline;
use Viserio\Routing\Middlewares\CallableMiddleware;

class Pipeline extends BasePipeline
{
    /**
     * The method to call on each stage.
     *
     * @var string
     */
    protected $method = 'process';

    /**
     * Get a Closure that represents a slice of the application onion.
     *
     * @return \Closure
     */
    protected function getSlice(): Closure
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                $slice = parent::getSlice();

                $callable = $slice(new CallableMiddleware($stack), $pipe);

                return $callable($passable);
            };
        };
    }
}
