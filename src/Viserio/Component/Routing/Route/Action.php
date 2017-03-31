<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Route;

use LogicException;
use Narrowspark\Arr\Arr;
use UnexpectedValueException;

class Action
{
    /**
     * Parse the given action into an array.
     *
     * @param string $uri
     * @param mixed  $action
     *
     * @return array
     */
    public static function parse(string $uri, $action): array
    {
        // If no action is passed in right away, we assume the user will make use of
        // fluent routing. In that case, we set a default closure, to be executed
        // if the user never explicitly sets an action to handle the given uri.
        if (is_null($action)) {
            return static::missingAction($uri);
        }

        // If the action is already a Closure instance, we will just set that instance
        // as the "uses" property.
        if (is_callable($action)) {
            return ['uses' => $action];
        }

        // If no "uses" property has been set, we will dig through the array to find a
        // Closure instance within this list. We will set the first Closure we come across.
        if (! isset($action['uses'])) {
            $action['uses'] = Arr::first($action, function ($key, $value) {
                return is_callable($value) && is_numeric($key);
            });
        }

        if (is_string($action['uses']) && mb_strpos($action['uses'], '@') === false) {
            if (! method_exists($action['uses'], '__invoke')) {
                throw new UnexpectedValueException(sprintf(
                    'Invalid route action: [%s]',
                    $action['uses']
                ));
            }

            $action['uses'] = $action['uses'] . '@__invoke';
        }

        return $action;
    }

    /**
     * Get an action for a route that has no action.
     *
     * @param string $uri
     *
     * @return array
     */
    protected static function missingAction(string $uri): array
    {
        return ['uses' => function () use ($uri) {
            throw new LogicException(sprintf('Route for [%s] has no action.', $uri));
        }];
    }
}
