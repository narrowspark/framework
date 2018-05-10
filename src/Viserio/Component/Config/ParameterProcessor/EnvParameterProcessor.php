<?php
declare(strict_types=1);
namespace Viserio\Component\Config\ParameterProcessor;

class EnvParameterProcessor extends AbstractParameterProcessor
{
    /**
     * Get the process reference key.
     *
     * @return string
     */
    public static function getReferenceKeyword(): string
    {
        return 'env';
    }

    /**
     * Process parameter through processor.
     *
     * @param string $parameter
     *
     * @return mixed
     */
    public function process(string $parameter)
    {
        $parameterKey = $this->parseParameter($parameter);

        return \getenv($parameterKey);
    }
}
