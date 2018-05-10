<?php
declare(strict_types=1);
namespace Viserio\Component\Config\ParameterProcessor;

use Viserio\Component\Contract\Config\ParameterProcessor as ParameterProcessorContract;

abstract class AbstractParameterProcessor implements ParameterProcessorContract
{
    /**
     * {@inheritdoc}
     */
    public function supports(string $parameter): bool
    {
        return \mb_strpos($parameter, '%' . static::getReferenceKeyword() . ':') === 0 && \mb_substr($parameter, -1) === '%';
    }

    /**
     * Get the value without the reference keyword.
     *
     * @param string $parameter
     *
     * @return string
     */
    protected function parseParameter(string $parameter): string
    {
        return \mb_substr($parameter, \mb_strlen(static::getReferenceKeyword()) + 2, -1);
    }
}
