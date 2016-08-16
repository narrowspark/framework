<?php
declare(strict_types=1);
namespace Viserio\Routing;

class Redirect
{
    /**
     * [$mode description].
     *
     * @var string
     */
    protected $mode;

    /**
     * [$routeName description].
     *
     * @var string
     */
    protected $routeName;

    /**
     * [$parameters description].
     *
     * @var array
     */
    protected $parameters;

    /**
     * [$location description].
     *
     * @var string
     */
    protected $location;

    /**
     * [$action description].
     *
     * @var callback
     */
    protected $action;

    /**
     * The URL generator instance.
     *
     * @var \Viserio\Routing\UrlGenerator
     */
    protected $generator;

    /**
     * Create a new Redirector instance.
     *
     * @param \Viserio\Routing\UrlGenerator
     */
    public function __construct(UrlGenerator $generator)
    {
        $this->generator = $generator;
    }
}
