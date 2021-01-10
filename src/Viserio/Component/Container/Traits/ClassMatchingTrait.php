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

namespace Viserio\Component\Container\Traits;

use Composer\Autoload\ClassLoader;
use Symfony\Component\Debug\DebugClassLoader as LegacyDebugClassLoader;
use Symfony\Component\ErrorHandler\DebugClassLoader;

trait ClassMatchingTrait
{
    /**
     * Expands the given class patterns using a list of existing classes.
     *
     * @param array $patterns The class patterns to expand
     * @param array $classes  The existing classes to match against the patterns
     */
    private function expandClasses(array $patterns, array $classes): array
    {
        $expanded = [];

        // Explicit classes declared in the patterns are returned directly
        foreach ($patterns as $key => $pattern) {
            if ('\\' !== \substr($pattern, -1) && false === \strpos($pattern, '*')) {
                unset($patterns[$key]);
                $expanded[] = \ltrim($pattern, '\\');
            }
        }

        // Match patterns with the classes list
        $regexps = $this->patternsToRegexps($patterns);

        foreach ($classes as $class) {
            $class = \ltrim($class, '\\');

            if ($this->matchAnyRegexps($class, $regexps)) {
                $expanded[] = $class;
            }
        }

        return \array_unique($expanded);
    }

    /**
     * Returns all found classes in the composer class map.
     */
    private function getClassesInComposerClassMaps(): array
    {
        $classes = [];

        foreach (\spl_autoload_functions() as $function) {
            if (! \is_array($function)) {
                continue;
            }

            if ($function[0] instanceof DebugClassLoader || $function[0] instanceof LegacyDebugClassLoader) {
                $function = $function[0]->getClassLoader();
            }

            if (\is_array($function) && $function[0] instanceof ClassLoader) {
                $classes += \array_filter($function[0]->getClassMap());
            }
        }

        return \array_keys($classes);
    }

    /**
     * Transform class patterns to regexps.
     */
    private function patternsToRegexps(array $patterns): array
    {
        $regexps = [];

        foreach ($patterns as $pattern) {
            // Escape user input
            $regex = \preg_quote(\ltrim($pattern, '\\'));

            // Wildcards * and **
            $regex = \strtr($regex, ['\\*\\*' => '.*?', '\\*' => '[^\\\\]*?']);

            // If this class does not end by a slash, anchor the end
            if ('\\' !== \substr($regex, -1)) {
                $regex .= '$';
            }

            $regexps[] = '{^\\\\' . $regex . '}';
        }

        return $regexps;
    }

    /**
     * Check if the given class match a regexps.
     */
    private function matchAnyRegexps(string $class, array $regexps): bool
    {
        $blacklisted = \strpos($class, 'Test') !== false;

        foreach ($regexps as $regex) {
            if ($blacklisted && \strpos($regex, 'Test') === false) {
                continue;
            }

            if (\preg_match($regex, '\\' . $class)) {
                return true;
            }
        }

        return false;
    }
}
