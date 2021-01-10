<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Routing\Route;

use Viserio\Contract\Routing\Exception\LogicException;
use Viserio\Contract\Routing\Exception\UnexpectedValueException;

class Action
{
    /**
     * Parse the given action into an array.
     *
     * @throws \Viserio\Contract\Routing\Exception\UnexpectedValueException if invalid route action
     * @throws \Viserio\Contract\Routing\Exception\LogicException           if no action found
     */
    public static function parse(string $uri, $action): array
    {
        // If no action is passed in right away, we assume the user will make use of
        // fluent routing. In that case, we set a default closure, to be executed
        // if the user never explicitly sets an action to handle the given uri.
        if ($action === null) {
            return static::missingAction($uri);
        }

        // If the action is already a Closure instance, we will just set that instance
        // as the "uses" property.
        if (\is_callable($action)) {
            return ['uses' => $action];
        }

        // If no "uses" property has been set, we will dig through the array to find a
        // Closure instance within this list. We will set the first Closure we come across.
        if (! isset($action['uses'])) {
            $callback = static function ($key, $value) {
                return \is_callable($value) && \is_numeric($key);
            };

            $action['uses'] = self::getFirst($action, $callback);
        }

        if (\is_string($action['uses']) && \strpos($action['uses'], '@') === false) {
            if (! \method_exists($action['uses'], '__invoke')) {
                throw new UnexpectedValueException(\sprintf('Invalid route action: [%s].', $action['uses']));
            }

            $action['uses'] = $action['uses'] . '@__invoke';
        }

        return $action;
    }

    /**
     * Get an action for a route that has no action.
     *
     * @throws \Viserio\Contract\Routing\Exception\LogicException if no action found
     */
    protected static function missingAction(string $uri): array
    {
        return ['uses' => static function () use ($uri): void {
            throw new LogicException(\sprintf('Route for [%s] has no action.', $uri));
        }];
    }

    /**
     * Return the first element in an array passing a given truth test.
     */
    protected static function getFirst(array $array, callable $callback)
    {
        foreach ($array as $key => $value) {
            if ($callback($key, $value)) {
                return $value;
            }
        }

        return null;
    }
}
