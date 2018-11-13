<?php
declare(strict_types=1);
namespace Narrowspark\Benchmark\Http;

use PhpBench\Benchmark\Metadata\Annotations\Groups;

/**
 * @BeforeMethods({"classSetUp"}, extend=true)
 * @Revs(3000)
 * @Iterations(10)
 * @OutputTimeUnit("microseconds", precision=3)
 */
abstract class AbstractHttpResponseBenchCase
{
    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    abstract public function classSetUp(): void;

    public function dataProviderWithHeader(): array
    {
        return [
            ['value' => 'value'],
            ['value' => ['value']],
            ['value' => ['value1', 'value2']],
            ['value' => ''],
        ];
    }

    /**
     * @ParamProviders({"dataProviderWithHeader"})
     * @Groups({"http-response-with-header"})
     *
     * @param array $params
     */
    public function benchWithHeader($params): void
    {
        $this->response->withHeader('Basic', $params['value']);
    }
}
