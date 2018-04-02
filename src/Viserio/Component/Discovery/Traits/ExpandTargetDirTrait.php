<?php
declare(strict_types=1);
namespace Viserio\Component\Discovery\Traits;

trait ExpandTargetDirTrait
{
    /**
     * @param array  $options
     * @param string $target
     *
     * @return string
     */
    public function expandTargetDir(array $options, string $target): string
    {
        return \preg_replace_callback('{%(.+?)%}', function ($matches) use ($options) {
            $option = \str_replace('_', '-', \mb_strtolower($matches[1]));

            if (! isset($options[$option])) {
                return $matches[0];
            }

            return \rtrim($options[$option], '/');
        }, $target);
    }
}
