<?php
namespace Viserio\Filesystem\Parser\Traits;

/**
 * IsGroupTrait.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6
 */
trait IsGroupTrait
{
    /**
     * Check if config belongs to a group.
     *
     * @param string|null $group
     * @param array       $data
     *
     * @return array
     */
    protected function isGroup($group = null, array $data = [])
    {
        $groupData = [];

        foreach ($data as $key => $value) {
            $name = sprintf('%s::%s', $group, $key);
            $groupData[$name] = $value;
        }

        return (array) $groupData;
    }
}
