<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Container\Helper;

use ReflectionClass;
use Viserio\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Contract\Container\Exception\LogicException;

final class Reflection
{
    /** @var array */
    private const BUILTIN_TYPES = [
        'string' => 1, 'int' => 1, 'float' => 1, 'bool' => 1, 'array' => 1, 'object' => 1,
        'callable' => 1, 'iterable' => 1, 'void' => 1, 'null' => 1,
    ];

    /**
     * @throws \Error
     */
    public function __construct()
    {
        throw new \Error('Class [' . \get_class($this) . '] is static and cannot be instantiated.');
    }

    /**
     * Expands class name into full name.
     *
     * @param string           $name
     * @param \ReflectionClass $rc
     *
     * @throws \Viserio\Contract\Container\Exception\InvalidArgumentException
     */
    public static function expandClassName(string $name, ReflectionClass $rc): string
    {
        if ($name === '') {
            throw new InvalidArgumentException('Class name cant be empty.');
        }

        $lower = \strtolower($name);

        if (isset(self::BUILTIN_TYPES[$lower])) {
            return $lower;
        }

        if ($lower === 'self') {
            return $rc->getName();
        }

        if ($name[0] === '\\') { // fully qualified name
            return \ltrim($name, '\\');
        }

        $uses = self::getUseStatements($rc);
        $parts = \explode('\\', $name, 2);

        if (isset($uses[$parts[0]])) {
            $parts[0] = $uses[$parts[0]];

            return \implode('\\', $parts);
        }

        if ($rc->inNamespace()) {
            return $rc->getNamespaceName() . '\\' . $name;
        }

        return $name;
    }

    /**
     * @param \ReflectionClass $class
     *
     * @return array of [alias => class]
     */
    public static function getUseStatements(ReflectionClass $class): array
    {
        if ($class->isAnonymous()) {
            throw new LogicException('Anonymous classes are not supported.');
        }

        static $cache = [];

        if (! isset($cache[$name = $class->getName()])) {
            if ($class->isInternal()) {
                $cache[$name] = [];
            } else {
                $code = \file_get_contents($class->getFileName());
                $cache = \array_merge(self::parseUseStatements($code, $name), $cache);
            }
        }

        return $cache[$name];
    }

    /**
     * Parses PHP code to [class => [alias => class, ...]].
     *
     * @param string      $code
     * @param null|string $forClass
     *
     * @return array
     */
    private static function parseUseStatements(string $code, string $forClass = null): array
    {
        try {
            $tokens = \token_get_all($code, \TOKEN_PARSE);
        } catch (\ParseError $e) {
            trigger_error($e->getMessage(), \E_USER_NOTICE);
            $tokens = [];
        }

        $namespace = $class = $classLevel = $level = null;
        $res = $uses = [];

        while ($token = \current($tokens)) {
            \next($tokens);

            switch (\is_array($token) ? $token[0] : $token) {
                case \T_NAMESPACE:
                    $namespace = \ltrim(self::fetch($tokens, [\T_STRING, \T_NS_SEPARATOR]) . '\\', '\\');
                    $uses = [];

                    break;

                case \T_CLASS:
                case \T_INTERFACE:
                case \T_TRAIT:
                    if ($name = self::fetch($tokens, \T_STRING)) {
                        $class = $namespace . $name;
                        $classLevel = $level + 1;
                        $res[$class] = $uses;

                        if ($class === $forClass) {
                            return $res;
                        }
                    }

                    break;

                case \T_USE:
                    while (! $class && ($name = self::fetch($tokens, [\T_STRING, \T_NS_SEPARATOR]))) {
                        $name = \ltrim($name, '\\');

                        if (self::fetch($tokens, '{')) {
                            while ($suffix = self::fetch($tokens, [\T_STRING, \T_NS_SEPARATOR])) {
                                if (self::fetch($tokens, \T_AS)) {
                                    $uses[self::fetch($tokens, \T_STRING)] = $name . $suffix;
                                } else {
                                    $tmp = \explode('\\', $suffix);
                                    $uses[\end($tmp)] = $name . $suffix;
                                }

                                if (! self::fetch($tokens, ',')) {
                                    break;
                                }
                            }
                        } elseif (self::fetch($tokens, \T_AS)) {
                            $uses[self::fetch($tokens, \T_STRING)] = $name;
                        } else {
                            $tmp = \explode('\\', $name);
                            $uses[\end($tmp)] = $name;
                        }

                        if (! self::fetch($tokens, ',')) {
                            break;
                        }
                    }

                    break;

                case \T_CURLY_OPEN:
                case \T_DOLLAR_OPEN_CURLY_BRACES:
                case '{':
                    $level++;

                    break;

                case '}':
                    if ($level === $classLevel) {
                        $class = $classLevel = null;
                    }
                    $level--;
            }
        }

        return $res;
    }

    /**
     * @param array           $tokens
     * @param string|string[] $take
     *
     * @return null|string
     */
    private static function fetch(array &$tokens, $take): ?string
    {
        $res = null;

        while ($token = \current($tokens)) {
            [$token, $s] = \is_array($token) ? $token : [$token, $token];

            if (\in_array($token, (array) $take, true)) {
                $res .= $s;
            } elseif (! \in_array($token, [\T_DOC_COMMENT, \T_WHITESPACE, \T_COMMENT], true)) {
                break;
            }

            \next($tokens);
        }

        return $res;
    }
}
