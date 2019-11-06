<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Narrowspark\Benchmark\OptionsResolver;

use const DIRECTORY_SEPARATOR;

/**
 * @BeforeMethods({"classSetUp"})
 * @Revs(10000)
 * @Iterations(10)
 * @Warmup(2)
 */
abstract class AbstractCase
{
    /** @var array */
    protected $config;

    /** @var bool */
    protected $isId = false;

    /** @var string */
    protected $configId;

    /**
     * Setup config and class.
     */
    public function classSetUp(): void
    {
        $this->config = $this->getTestConfig();
        $this->configId = $this->isId ? 'orm_default' : null;
    }

    /**
     * Returns test config.
     *
     * @return array
     */
    protected function getTestConfig(): array
    {
        return require \dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Viserio' . DIRECTORY_SEPARATOR . 'Component' . DIRECTORY_SEPARATOR . 'OptionsResolver' . DIRECTORY_SEPARATOR . 'Tests' . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'testing.config.php';
    }
}
