<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Route;

class Group
{
    /**
     * Merge route groups into a new array.
     *
     * @param array $new
     * @param array $old
     *
     * @return array
     */
    public static function merge(array $new, array $old): array
    {
        if (isset($new['domain'])) {
            unset($old['domain']);
        }

        $new = \array_merge(static::formatAs($new, $old), [
            'namespace' => static::formatNamespace($new, $old),
            'prefix'    => static::formatGroupPrefix($new, $old),
            'where'     => static::formatWhere($new, $old),
            'suffix'    => static::formatGroupSuffix($new, $old),
        ]);

        foreach (['namespace', 'prefix', 'suffix', 'where', 'as'] as $name) {
            if (isset($old[$name])) {
                unset($old[$name]);
            }
        }

        return \array_merge_recursive($old, $new);
    }

    /**
     * Format the uses prefix for the new group attributes.
     *
     * @param array $new
     * @param array $old
     *
     * @return null|string
     */
    protected static function formatNamespace(array $new, array $old): ?string
    {
        if (isset($new['namespace'])) {
            if (\strpos($new['namespace'], '\\') === 0) {
                return \trim($new['namespace'], '\\');
            }

            return isset($old['namespace']) ?
                \trim($old['namespace'], '\\') . '\\' . \trim($new['namespace'], '\\') :
                \trim($new['namespace'], '\\');
        }

        return $old['namespace'] ?? null;
    }

    /**
     * Format the prefix for the new group attributes.
     *
     * @param array $new
     * @param array $old
     *
     * @return null|string
     */
    protected static function formatGroupPrefix(array $new, array $old): ?string
    {
        $oldPrefix = $old['prefix'] ?? null;

        if (isset($new['prefix'])) {
            return \trim($oldPrefix, '/') . '/' . \trim($new['prefix'], '/');
        }

        return $oldPrefix;
    }

    /**
     * Format the suffix for the new group attributes.
     *
     * @param array $new
     * @param array $old
     *
     * @return null|string
     */
    protected static function formatGroupSuffix(array $new, array $old): ?string
    {
        $oldSuffix = $old['suffix'] ?? null;

        if (isset($new['suffix'])) {
            return \trim($new['suffix']) . \trim($oldSuffix);
        }

        return $oldSuffix;
    }

    /**
     * Format the "wheres" for the new group attributes.
     *
     * @param array $new
     * @param array $old
     *
     * @return array
     */
    protected static function formatWhere(array $new, array $old): array
    {
        return \array_merge(
            $old['where'] ?? [],
            $new['where'] ?? []
        );
    }

    /**
     * Format the "as" clause of the new group attributes.
     *
     * @param array $new
     * @param array $old
     *
     * @return array
     */
    protected static function formatAs(array $new, array $old): array
    {
        if (isset($old['as'])) {
            $new['as'] = $old['as'] . ($new['as'] ?? '');
        }

        return $new;
    }
}
