<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Helper;

use Symfony\Component\Console\Descriptor\ApplicationDescription;
use Symfony\Component\Console\Descriptor\DescriptorInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Command\AbstractCommand;

class TextDescriptor implements DescriptorInterface
{
    /**
     * Describes an object if supported.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface  $output
     * @param \Viserio\Component\Console\Command\AbstractCommand $object
     * @param array                                              $options
     *
     * @return void
     */
    public function describe(OutputInterface $output, $object, array $options = []): void
    {
        /** @var Application $application */
        $application = $object->getApplication();

        $this->describeTitle($application, $output);

        $describedNamespace = $options['namespace'] ?? null;

        if ($describedNamespace !== null) {
            $output->write(\sprintf(
                "<comment>Available commands for the [%s] namespace</comment>\n\n",
                $describedNamespace
            ));
        }

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
        $name    = $application->getName();
        $version = $application->getVersion();

        if ($name === 'UNKNOWN' && $version === 'UNKNOWN') {
            return;
        }

        $output->write(
            \sprintf(
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

        $output->write("<fg=yellow;options=bold>USAGE:</> ${binary} <command> [options] [arguments]\n\n");
        $output->write("where <command> is one of:\n");
    }

    /**
     * Describes the application commands.
     *
     * @param \Viserio\Component\Console\Application             $application
     * @param \Viserio\Component\Console\Command\AbstractCommand $command
     * @param array                                              $options
     *
     * @return void
     */
    private function describeCommands(Application $application, AbstractCommand $command, array $options): void
    {
        $description = new ApplicationDescription(
            $application,
            $options['namespace'] ?? null,
            $options['show-hidden'] ?? false
        );

        Table::setStyleDefinition('zero', self::getZeroBorderStyle());

        $rows                        = [];
        $namespaceSortedCommandInfos = $this->getNamespaceSortedCommandInfos(
            $description->getCommands()
        );

        foreach ($namespaceSortedCommandInfos as $namespace => $infos) {
            $stringCommands     = '';
            $stringDescriptions = '';

            foreach ($infos as $info) {
                $description = '';

                if ($options['show-description'] ?? false) {
                    $description = $info['description'];
                }

                $stringCommands     .= '<fg=green>' . $info['command'] . "</>\n";
                $stringDescriptions .= $description . "\n";
            }

            $rows[] = [$namespace, $stringCommands, $stringDescriptions];
        }

        $command->table([], $rows, 'zero');
    }

    /**
     * Sort all application commands on namespace.
     *
     * @param array $commands
     *
     * @return array
     */
    private function getNamespaceSortedCommandInfos(array $commands): array
    {
        $namespaceSortedInfos = [];
        $regex                = '/^(.*)\:/';
        $binary               = Application::cerebroBinary();

        /** @var AbstractCommand $command */
        foreach ($commands as $name => $command) {
            \preg_match($regex, $name, $matches, \PREG_OFFSET_CAPTURE);

            $commandInfo = [
                'command'     => $binary . ' ' . $command->getSynopsis(),
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
        $style->setHorizontalBorderChars(' ');
        $style->setVerticalBorderChars(' ');
        $style->setDefaultCrossingChar(' ');

        return $style;
    }
}
