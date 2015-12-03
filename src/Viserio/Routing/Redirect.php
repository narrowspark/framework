<?php
namespace Viserio\Routing;

/**
 * Redirect.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
 */
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
     * RouteCollection instance.
     *
     * @var \Viserio\Routing\RouteCollection
     */
    protected $route;

    public function __construct(RouteCollection $route)
    {
        $this->route = $route;
    }

    /**
     * [to description].
     *
     * @param string $location
     *
     * @return self
     */
    public function to($location)
    {
        $this->mode = 'redirect';
        $this->location = $location;

        return $this;
    }

    /**
     * [toRoute description].
     *
     * @param string $routeName  [description]
     * @param array  $parameters
     *
     * @return self
     */
    public function toRoute($routeName, array $parameters = [])
    {
        $this->mode = 'named';
        $this->routeName = $routeName;
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * [toAction description].
     *
     * @param [type] $action     [description]
     * @param array  $parameters
     *
     * @return self
     */
    public function toAction($action, array $parameters = [])
    {
        $this->mode = 'action';
        $this->action = $action;
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * [with description].
     *
     * @param $key
     * @param $value
     *
     * @internal param $ [type] $key   [description]
     * @internal param $ [type] $value [description]
     *
     * @return $this [type] [description]
     */
    public function with($key, $value)
    {
        //session

        return $this;
    }

    /**
     * [execute description].
     *
     * @return bool
     */
    public function execute()
    {
        switch ($this->mode) {
            case 'redirect':
                header('Location: '.$this->location);
                break;

            case 'named':
                $this->route->runNamed($this->routeName, $this->parameters);
                break;

            case 'action':
                if (is_string($this->action)) {
                    $this->stringToCallback($this->action);
                }

                $this->route->execute($this->action, $this->parameters);
                break;
        }

        return false;
    }

    /**
     * [stringToCallback description].
     *
     * @param string $callback
     *
     * @throws \Exception
     *
     * @return bool
     */
    protected function stringToCallback(&$callback)
    {
        if (substr_count($callback, '::') === 1) {
            $callback = explode('::', $callback);

            return true;
        }

        throw new \Exception('Invalid callback: '.$callback);
    }
}
