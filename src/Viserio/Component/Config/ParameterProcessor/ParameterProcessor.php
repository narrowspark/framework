<?php
declare(strict_types=1);
namespace Viserio\Component\Config\ParameterProcessor;

use Narrowspark\Arr\Arr;

class ParameterProcessor extends AbstractParameterProcessor
{
    /**
     * A array of parameters.
     *
     * @var array
     */
    private $parameters;

    /**
     * Create a new ParameterProcessor instance.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Get the process reference key.
     *
     * @return string
     */
    public static function getReferenceKeyword(): string
    {
        return 'parameter';
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

        return Arr::get($this->parameters, $parameterKey);
    }
}
