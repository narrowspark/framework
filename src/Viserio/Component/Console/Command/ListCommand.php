<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Command;

use Symfony\Component\Console\Command\ListCommand as BaseListCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends BaseListCommand
{
    /**
     * The supported format.
     */
    private const FORMAT = 'txt';

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        if ($input->getOption('format') === static::FORMAT && ! $input->getOption('raw')) {

        } else {
            parent::execute($input, $output);
        }
    }
}
