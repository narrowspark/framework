<?php

namespace Brainwave\Console\Command;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Brainwave\Console\Input\InputArgument;
use Brainwave\Console\Input\InputOption;
use Brainwave\Contracts\Console\InvalidCommandExpression;
use Brainwave\Support\Str;

/**
 * ExpressionParser.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class ExpressionParser
{
    /**
     * @param string $expression
     *
     * @return array
     */
    public function parse($expression)
    {
        $tokens = explode(' ', $expression);
        $tokens = array_map('trim', $tokens);
        $tokens = array_values(array_filter($tokens));

        if (count($tokens) === 0) {
            throw new InvalidCommandExpression('The expression was empty');
        }

        $name = array_shift($tokens);
        $arguments = [];
        $options = [];

        foreach ($tokens as $token) {
            if (Str::startsWith($token, '--')) {
                throw new InvalidCommandExpression('An option must be enclosed by brackets: [--option]');
            }

            if ($this->isOption($token)) {
                $options[] = $this->parseOption($token);
            } else {
                $arguments[] = $this->parseArgument($token);
            }
        }

        return [
            'name' => $name,
            'arguments' => $arguments,
            'options' => $options,
        ];
    }

    protected function isOption($token)
    {
        return Str::startsWith($token, '[-');
    }

    protected function parseArgument($token)
    {
        if (Str::endsWith($token, ']*')) {
            $mode = InputArgument::IS_ARRAY;
            $name = trim($token, '[]*');
        } elseif (Str::endsWith($token, '*')) {
            $mode = InputArgument::IS_ARRAY | InputArgument::REQUIRED;
            $name = trim($token, '*');
        } elseif (Str::startsWith($token, '[')) {
            $mode = InputArgument::OPTIONAL;
            $name = trim($token, '[]');
        } else {
            $mode = InputArgument::REQUIRED;
            $name = $token;
        }

        return new InputArgument($name, $mode);
    }

    protected function parseOption($token)
    {
        $token = trim($token, '[]');

        // Shortcut [-y|--yell]
        if (strpos($token, '|') !== false) {
            list($shortcut, $token) = explode('|', $token, 2);
            $shortcut = ltrim($shortcut, '-');
        } else {
            $shortcut = null;
        }

        $name = ltrim($token, '-');

        if (Str::endsWith($token, '=]*')) {
            $mode = InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY;
            $name = substr($name, 0, -3);
        } elseif (Str::endsWith($token, '=')) {
            $mode = InputOption::VALUE_REQUIRED;
            $name = rtrim($name, '=');
        } else {
            $mode = InputOption::VALUE_NONE;
        }

        return new InputOption($name, $shortcut, $mode);
    }
}
