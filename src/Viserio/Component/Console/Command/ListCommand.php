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

namespace Viserio\Component\Console\Command;

use Viserio\Component\Console\Helper\DescriptorHelper;

/**
 * @internal
 */
final class ListCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'list';

    /**
     * {@inheritdoc}
     */
    protected $signature = 'list
        [namespace? : The namespace name.]
        [--show-description : Show command descriptions on the output list.]
        [--show-hidden : Show hidden commands on the output list.]
        [--raw : To output raw command list.]
        [--format=txt : The output format (txt, xml, json, or md).]
    ';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Lists console commands';

    /**
     * {@inheritdoc}
     */
    protected $hidden = true;

    /**
     * {@inheritdoc}
     */
    public function handle(): int
    {
        $helper = new DescriptorHelper();

        $helper->describe(
            $this->getOutput(),
            $this,
            [
                'format' => $this->option('format'),
                'raw_text' => $this->option('raw'),
                'show-description' => $this->option('show-description'),
                'namespace' => $this->argument('namespace'),
            ]
        );

        return 0;
    }
}
