<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Config\ParameterProcessor;

use Viserio\Component\Config\ParameterProcessor\AbstractParameterProcessor;
use Viserio\Component\Support\Env;

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

        return Env::get($parameterKey);
    }
}
