<?php
namespace Viserio\Filesystem\Parser\Traits;

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

/**
 * IsGroupTrait.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
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
