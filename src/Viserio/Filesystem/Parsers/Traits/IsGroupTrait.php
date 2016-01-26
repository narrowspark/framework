<?php
namespace Viserio\Filesystem\Parsers\Traits;

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
