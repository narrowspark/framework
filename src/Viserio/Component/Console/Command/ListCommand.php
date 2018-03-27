<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Command;

use Viserio\Component\Console\Helper\DescriptorHelper;

class ListCommand extends Command
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
    public function handle(): void
    {
        $helper = new DescriptorHelper();

        $helper->describe(
            $this->getOutput(),
            $this,
            [
                'format'      => $this->option('format'),
                'raw_text'    => $this->option('raw'),
                'description' => $this->option('description'),
                'namespace'   => $this->argument('namespace'),
            ]
        );
    }
}
