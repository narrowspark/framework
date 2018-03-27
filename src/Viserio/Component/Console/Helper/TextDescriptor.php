<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Helper;

use Symfony\Component\Console\Descriptor\DescriptorInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Command\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class TextDescriptor implements DescriptorInterface
{
    /**
     * {@inheritdoc}
     */
    public function describe(OutputInterface $output, $object, array $options = []): void
    {
        /* @var Application $application */
        $application = $object->getApplication();

        $this->describeTitle($application, $output);
        $this->describeUsage($output);
        $this->describeCommands($application, $object, $options);
    }

    /**
     * Describes the application title.
     *
     * @param \Viserio\Component\Console\Application            $application
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    private function describeTitle(Application $application, OutputInterface $output): void
    {
        $name = $application->getName();
        $version = $application->getVersion();

        if ($name === 'UNKNOWN' && $version === 'UNKNOWN') {
            return;
        }

        $output->write(
            sprintf(
                "\n<fg=white;options=bold>%s </> <fg=green;options=bold>%s</>\n\n",
                $name,
                $version
            )
        );
    }

    /**
     * Describes the application title.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    private function describeUsage(OutputInterface $output): void
    {
        $binary = Application::cerebroBinary();

        $output->write("<fg=yellow;options=bold>USAGE:</> $binary <command> [options] [arguments]\n\n");
        $output->write("where <command> is one of:\n");
    }

    /**
     * Describes the application commands.
     *
     * @param \Viserio\Component\Console\Application     $application
     * @param \Viserio\Component\Console\Command\Command $command
     * @param array                                      $options
     *
     * @return void
     */
    private function describeCommands(Application $application, Command $command, array $options): void
    {
        $commands = array_filter($application->all(), function (SymfonyCommand $applicationCommand) {
            return ! $applicationCommand->isHidden();
        });

        Table::setStyleDefinition('zero', self::getZeroBorderStyle());

        $rows = [];

        foreach ($this->getNamespaceSortedCommands($commands) as $namespace => $infos) {
            $stringCommands = '';
            $stringDescriptions = '';

            foreach ($infos as $info) {
                $description = '';

                if (isset($options['description']) && $options['description'] === true) {
                    $description = $info['description'];
                }

                $stringCommands     .= '<fg=green>' . $info['command'] . "</>\n";
                $stringDescriptions .= $description . "\n";
            }

            $rows[] = [$namespace, $stringCommands, $stringDescriptions];
        }

        $command->table([], $rows, 'zero');
    }

    private function getNamespaceSortedCommands(array $commands): array
    {
        $namespaceSortedInfos = [];
        $regex           = '/^(.*)\:/';
        $binary          = Application::cerebroBinary();

        foreach ($commands as $name => $command) {
            preg_match($regex, $name, $matches, PREG_OFFSET_CAPTURE);

            $commandInfo = [
                'command' => $binary . ' ' . $command->getName(),
                'description' => $command->getDescription(),
            ];

            if (\count($matches) === 0) {
                $namespaceSortedInfos[$name][] = $commandInfo;
            } else {
                $namespaceSortedInfos[$matches[1][0]][] = $commandInfo;
            }
        }

        return $namespaceSortedInfos;
    }

    /**
     * @return TableStyle
     */
    private static function getZeroBorderStyle(): TableStyle
    {
        $style = new TableStyle();
        $style->setHorizontalBorderChar(' ');
        $style->setVerticalBorderChar(' ');
        $style->setCrossingChar(' ');

        return $style;
    }
}
