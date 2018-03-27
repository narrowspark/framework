<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Helper;

use Symfony\Component\Console\Helper\DescriptorHelper as BaseDescriptorHelper;
use Symfony\Component\Console\Output\OutputInterface;

class DescriptorHelper extends BaseDescriptorHelper
{
    /**
     * {@inheritdoc}
     */
    public function describe(OutputInterface $output, $object, array $options = []): void
    {
        $options = array_merge([
            'raw_text' => false,
            'format'   => 'txt',
        ], $options);

        if ($options['raw_text'] === false && $options['format'] === 'txt') {
            $this->register('txt', new TextDescriptor());
        }

        parent::describe($output, $object, $options);
    }
}
