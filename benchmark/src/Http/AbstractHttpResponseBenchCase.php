<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Narrowspark\Benchmark\Http;

use PhpBench\Benchmark\Metadata\Annotations\Groups;

/**
 * @BeforeMethods({"classSetUp"}, extend=true)
 * @Revs(3000)
 * @Iterations(50)
 * @OutputTimeUnit("microseconds", precision=3)
 */
abstract class AbstractHttpResponseBenchCase
{
    /** @var \Psr\Http\Message\ResponseInterface */
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
