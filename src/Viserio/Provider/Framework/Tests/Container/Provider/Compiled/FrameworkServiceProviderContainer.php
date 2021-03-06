<?php

declare(strict_types=1);

namespace Viserio\Provider\Framework\Tests\Container\Provider\Compiled;

/**
 * This class has been auto-generated by Viserio Container Component.
 */
final class FrameworkServiceProviderContainer extends \Viserio\Component\Container\AbstractCompiledContainer
{
    /**
     * Create a new Compiled Container instance.
     */
    public function __construct()
    {
        $this->services = $this->privates = [];
        $this->parameters = [
            'viserio.container.runtime.parameter.processor.types' => [
                'base64' => [
                    0 => 'string',
                ],
                'base64_decode' => [
                    0 => 'string',
                ],
                'const' => [
                    0 => 'bool',
                    1 => 'int',
                    2 => 'float',
                    3 => 'string',
                    4 => 'array',
                ],
                'csv' => [
                    0 => 'array',
                ],
                'str_getcsv' => [
                    0 => 'array',
                ],
                'file' => [
                    0 => 'string',
                ],
                'require' => [
                    0 => 'bool',
                    1 => 'int',
                    2 => 'float',
                    3 => 'string',
                    4 => 'array',
                ],
                'json' => [
                    0 => 'array',
                ],
                'json_decode' => [
                    0 => 'array',
                ],
                'bool' => [
                    0 => 'bool',
                ],
                'float' => [
                    0 => 'float',
                ],
                'int' => [
                    0 => 'int',
                ],
                'string' => [
                    0 => 'string',
                ],
                'trim' => [
                    0 => 'string',
                ],
                'url' => [
                    0 => 'array',
                ],
                'query_string' => [
                    0 => 'array',
                ],
                'directory' => [
                    0 => 'string',
                ],
                'env' => [
                    0 => 'bool',
                    1 => 'int',
                    2 => 'float',
                    3 => 'string',
                    4 => 'array',
                ],
            ],
        ];
        $this->methodMapping = [
            \Viserio\Component\Container\Processor\Base64ParameterProcessor::class => 'get29807369f2cb20168567cd8c9c3307a999a9572cda920acaeca48ab20c5a1987',
            \Viserio\Component\Container\Processor\ConstantProcessor::class => 'get2b5b066e6dfe4b5365f121aca98b45f3fba9e23e7298998f9f1deaa447cccc03',
            \Viserio\Component\Container\Processor\CsvParameterProcessor::class => 'get5c03cfaba0ef990c83126eef6fa66c15e1f3a2362dab02736f3c2017285b593e',
            \Viserio\Component\Container\Processor\EnvParameterProcessor::class => 'getf2b69a4533503376ddade957f0f4242967826e7f25fb7a36e11e0b3441e0735b',
            \Viserio\Component\Container\Processor\FileParameterProcessor::class => 'get756a0c60d5920b31cfc2bc628b15b1ea70780611b355a90cb2087c44c3a02e78',
            \Viserio\Component\Container\Processor\JsonParameterProcessor::class => 'get9e0e9d9db4c9b8d40aea977458e56cb4c5bd9304c6f00f96e938c28579115a4c',
            \Viserio\Component\Container\Processor\PhpTypeParameterProcessor::class => 'get2da684770948d9fcbf97ffd088bcc2bd665052e76f56071a357041cc707d0499',
            \Viserio\Component\Container\Processor\UrlParameterProcessor::class => 'get54a2b2097bffe0f6536819333066982e07ff6bb2b1b72957c105b84b94f8472d',
            \Viserio\Provider\Framework\Container\Processor\DirectoryParameterProcessor::class => 'get1cf73f9a73ef3f95069e678495738f0ae58843aeb24f4adad2729e6afd4fa8ea',
            'viserio.container.runtime.parameter.processors' => 'gete72852784fbbbd62bbac5a8aec7e44d0acb18db0dd6a35a841780313b22837ff',
        ];
    }

    /**
     * Returns the public Viserio\Component\Container\Processor\Base64ParameterProcessor shared service.
     *
     * @return \Viserio\Component\Container\Processor\Base64ParameterProcessor
     */
    protected function get29807369f2cb20168567cd8c9c3307a999a9572cda920acaeca48ab20c5a1987(): \Viserio\Component\Container\Processor\Base64ParameterProcessor
    {
        return $this->services[\Viserio\Component\Container\Processor\Base64ParameterProcessor::class] = new \Viserio\Component\Container\Processor\Base64ParameterProcessor();
    }

    /**
     * Returns the public Viserio\Component\Container\Processor\ConstantProcessor shared service.
     *
     * @return \Viserio\Component\Container\Processor\ConstantProcessor
     */
    protected function get2b5b066e6dfe4b5365f121aca98b45f3fba9e23e7298998f9f1deaa447cccc03(): \Viserio\Component\Container\Processor\ConstantProcessor
    {
        return $this->services[\Viserio\Component\Container\Processor\ConstantProcessor::class] = new \Viserio\Component\Container\Processor\ConstantProcessor();
    }

    /**
     * Returns the public Viserio\Component\Container\Processor\CsvParameterProcessor shared service.
     *
     * @return \Viserio\Component\Container\Processor\CsvParameterProcessor
     */
    protected function get5c03cfaba0ef990c83126eef6fa66c15e1f3a2362dab02736f3c2017285b593e(): \Viserio\Component\Container\Processor\CsvParameterProcessor
    {
        return $this->services[\Viserio\Component\Container\Processor\CsvParameterProcessor::class] = new \Viserio\Component\Container\Processor\CsvParameterProcessor();
    }

    /**
     * Returns the public Viserio\Component\Container\Processor\EnvParameterProcessor shared service.
     *
     * @return \Viserio\Component\Container\Processor\EnvParameterProcessor
     */
    protected function getf2b69a4533503376ddade957f0f4242967826e7f25fb7a36e11e0b3441e0735b(): \Viserio\Component\Container\Processor\EnvParameterProcessor
    {
        return $this->services[\Viserio\Component\Container\Processor\EnvParameterProcessor::class] = new \Viserio\Component\Container\Processor\EnvParameterProcessor();
    }

    /**
     * Returns the public Viserio\Component\Container\Processor\FileParameterProcessor shared service.
     *
     * @return \Viserio\Component\Container\Processor\FileParameterProcessor
     */
    protected function get756a0c60d5920b31cfc2bc628b15b1ea70780611b355a90cb2087c44c3a02e78(): \Viserio\Component\Container\Processor\FileParameterProcessor
    {
        return $this->services[\Viserio\Component\Container\Processor\FileParameterProcessor::class] = new \Viserio\Component\Container\Processor\FileParameterProcessor();
    }

    /**
     * Returns the public Viserio\Component\Container\Processor\JsonParameterProcessor shared service.
     *
     * @return \Viserio\Component\Container\Processor\JsonParameterProcessor
     */
    protected function get9e0e9d9db4c9b8d40aea977458e56cb4c5bd9304c6f00f96e938c28579115a4c(): \Viserio\Component\Container\Processor\JsonParameterProcessor
    {
        return $this->services[\Viserio\Component\Container\Processor\JsonParameterProcessor::class] = new \Viserio\Component\Container\Processor\JsonParameterProcessor();
    }

    /**
     * Returns the public Viserio\Component\Container\Processor\PhpTypeParameterProcessor shared service.
     *
     * @return \Viserio\Component\Container\Processor\PhpTypeParameterProcessor
     */
    protected function get2da684770948d9fcbf97ffd088bcc2bd665052e76f56071a357041cc707d0499(): \Viserio\Component\Container\Processor\PhpTypeParameterProcessor
    {
        return $this->services[\Viserio\Component\Container\Processor\PhpTypeParameterProcessor::class] = new \Viserio\Component\Container\Processor\PhpTypeParameterProcessor();
    }

    /**
     * Returns the public Viserio\Component\Container\Processor\UrlParameterProcessor shared service.
     *
     * @return \Viserio\Component\Container\Processor\UrlParameterProcessor
     */
    protected function get54a2b2097bffe0f6536819333066982e07ff6bb2b1b72957c105b84b94f8472d(): \Viserio\Component\Container\Processor\UrlParameterProcessor
    {
        return $this->services[\Viserio\Component\Container\Processor\UrlParameterProcessor::class] = new \Viserio\Component\Container\Processor\UrlParameterProcessor();
    }

    /**
     * Returns the public Viserio\Provider\Framework\Container\Processor\DirectoryParameterProcessor shared service.
     *
     * @return \Viserio\Provider\Framework\Container\Processor\DirectoryParameterProcessor
     */
    protected function get1cf73f9a73ef3f95069e678495738f0ae58843aeb24f4adad2729e6afd4fa8ea(): \Viserio\Provider\Framework\Container\Processor\DirectoryParameterProcessor
    {
        return $this->services[\Viserio\Provider\Framework\Container\Processor\DirectoryParameterProcessor::class] = new \Viserio\Provider\Framework\Container\Processor\DirectoryParameterProcessor(new \Viserio\Component\Config\Container\Definition\ConfigDefinition(), $this);
    }

    /**
     * Returns the public viserio.container.runtime.parameter.processors shared service.
     *
     * @return \Viserio\Component\Container\RewindableGenerator
     */
    protected function gete72852784fbbbd62bbac5a8aec7e44d0acb18db0dd6a35a841780313b22837ff(): \Viserio\Component\Container\RewindableGenerator
    {
        return $this->services['viserio.container.runtime.parameter.processors'] = new \Viserio\Component\Container\RewindableGenerator(function () {
            yield 0 => ($this->services[\Viserio\Component\Container\Processor\Base64ParameterProcessor::class] ?? $this->get29807369f2cb20168567cd8c9c3307a999a9572cda920acaeca48ab20c5a1987());
            yield 1 => ($this->services[\Viserio\Component\Container\Processor\ConstantProcessor::class] ?? $this->get2b5b066e6dfe4b5365f121aca98b45f3fba9e23e7298998f9f1deaa447cccc03());
            yield 2 => ($this->services[\Viserio\Component\Container\Processor\CsvParameterProcessor::class] ?? $this->get5c03cfaba0ef990c83126eef6fa66c15e1f3a2362dab02736f3c2017285b593e());
            yield 3 => ($this->services[\Viserio\Component\Container\Processor\FileParameterProcessor::class] ?? $this->get756a0c60d5920b31cfc2bc628b15b1ea70780611b355a90cb2087c44c3a02e78());
            yield 4 => ($this->services[\Viserio\Component\Container\Processor\JsonParameterProcessor::class] ?? $this->get9e0e9d9db4c9b8d40aea977458e56cb4c5bd9304c6f00f96e938c28579115a4c());
            yield 5 => ($this->services[\Viserio\Component\Container\Processor\PhpTypeParameterProcessor::class] ?? $this->get2da684770948d9fcbf97ffd088bcc2bd665052e76f56071a357041cc707d0499());
            yield 6 => ($this->services[\Viserio\Component\Container\Processor\UrlParameterProcessor::class] ?? $this->get54a2b2097bffe0f6536819333066982e07ff6bb2b1b72957c105b84b94f8472d());
            yield 7 => ($this->services[\Viserio\Provider\Framework\Container\Processor\DirectoryParameterProcessor::class] ?? $this->get1cf73f9a73ef3f95069e678495738f0ae58843aeb24f4adad2729e6afd4fa8ea());
            yield 8 => ($this->services[\Viserio\Component\Container\Processor\EnvParameterProcessor::class] ?? $this->getf2b69a4533503376ddade957f0f4242967826e7f25fb7a36e11e0b3441e0735b());
        }, 9);
    }

    /**
     * {@inheritdoc}
     */
    public function getRemovedIds(): array
    {
        return [
            \Psr\Container\ContainerInterface::class => true,
            \Viserio\Contract\Container\CompiledContainer::class => true,
            \Viserio\Contract\Container\Factory::class => true,
            \Viserio\Contract\Container\TaggedContainer::class => true,
            'container' => true,
            'viserio.container.parameter.processors' => true,
        ];
    }
}
