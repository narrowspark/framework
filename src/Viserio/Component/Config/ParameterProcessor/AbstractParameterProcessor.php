<?php
declare(strict_types=1);
namespace Viserio\Component\Config\ParameterProcessor;

use Viserio\Component\Contract\Config\ParameterProcessor as ParameterProcessorContract;

abstract class AbstractParameterProcessor implements ParameterProcessorContract
{
    public function supports(string $parameter): bool
    {
        return \mb_strpos($parameter, $this->getReferenceKeyword() . '(') === 0 && \mb_substr($parameter, -1) === ')';
    }

    protected function parseParameter(string $parameter): string
    {
        return \mb_substr($parameter, \mb_strlen($this->getReferenceKeyword()) + 1, -1);
    }
}
