<?php
namespace Viserio\Console\Command;

use Viserio\Console\Input\InputArgument;
use Viserio\Console\Input\InputOption;
use Viserio\Contracts\Console\InvalidCommandExpression;
use Viserio\Support\Str;

class ExpressionParser
{
    /**
     * @param string $expression
     *
     * @return array
     */
    public function parse(string $expression): array
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
