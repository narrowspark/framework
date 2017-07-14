<?php
declare(strict_types=1);
namespace Viserio\Component\View\Traits;

use Viserio\Component\Contracts\View\Finder as FinderContract;

trait NormalizeNameTrait
{
    /**
     * Normalize a view name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function normalizeName(string $name): string
    {
        $delimiter = FinderContract::HINT_PATH_DELIMITER;

        if (mb_strpos($name, $delimiter) === false) {
            return str_replace('/', '.', $name);
        }

        [$namespace, $name] = explode($delimiter, $name);

        return $namespace . $delimiter . str_replace('/', '.', $name);
    }
}
