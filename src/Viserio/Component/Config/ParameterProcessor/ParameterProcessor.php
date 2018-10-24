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
     * {@inheritdoc}
     */
    public static function getReferenceKeyword(): string
    {
        return 'parameter';
    }

    /**
     * {@inheritdoc}
     */
    public function process(string $parameter)
    {
        $parameterKey = $this->parseParameter($parameter);

        return Arr::get($this->parameters, $parameterKey);
    }
}
