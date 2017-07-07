<?php
declare(strict_types=1);
namespace Viserio\Component\Routing;

use Viserio\Component\Contracts\Routing\Router as RouterContract;

class ResourceRegistrar
{
    /**
     * The router instance.
     *
     * @var \Viserio\Component\Contracts\Routing\Router
     */
    protected $router;

    /**
     * The default actions for a resourceful controller.
     *
     * @var array
     */
    protected $resourceDefaults = [
        'index',
        'create',
        'store',
        'show',
        'edit',
        'update',
        'destroy',
    ];

    /**
     * The parameters set for this resource instance.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * The global parameter mapping.
     *
     * @var array
     */
    protected static $parameterMap = [];

    /**
     * Singular global parameters.
     *
     * @var bool
     */
    protected static $singularParameters = true;

    /**
     * The verbs used in the resource URIs.
     *
     * @var array
     */
    protected static $verbs = [
        'create' => 'create',
        'edit'   => 'edit',
    ];

    /**
     * Create a new resource registrar instance.
     *
     * @param \Viserio\Component\Contracts\Routing\Router $router
     */
    public function __construct(RouterContract $router)
    {
        $this->router = $router;
    }
}
