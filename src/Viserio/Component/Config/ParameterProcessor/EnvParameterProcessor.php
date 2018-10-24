<?php
declare(strict_types=1);
namespace Viserio\Component\Config\ParameterProcessor;

class EnvParameterProcessor extends AbstractParameterProcessor
{
    /**
     * {@inheritdoc}
     */
    public static function getReferenceKeyword(): string
    {
        return 'env';
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $parameter)
    {
        $parameterKey = $this->parseParameter($parameter);

        return \getenv($parameterKey);
    }
}
