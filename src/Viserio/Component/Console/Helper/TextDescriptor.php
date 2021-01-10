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

namespace Viserio\Component\Console\Helper;

use Symfony\Component\Console\Descriptor\ApplicationDescription;
use Symfony\Component\Console\Descriptor\DescriptorInterface;
use Symfony\Component\Console\Exception\InvalidArgumentException;
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
     * @param object|\Viserio\Component\Console\Command\AbstractCommand $object
     */
    public function describe(OutputInterface $output, $object, array $options = []): void
    {
        if (! $object instanceof AbstractCommand) {
            throw new InvalidArgumentException(\sprintf('Object of type "%s" is not describable.', \get_class($object)));
        }

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
     */
    private function describeTitle(Application $application, OutputInterface $output): void
    {
        $name = $application->getName();
        $version = $application->getVersion();

        if ($name === 'UNKNOWN' && $version === 'UNKNOWN') {
            return;
        }

        $appEnv = \getenv('APP_ENV');
        $appDebug = \getenv('APP_DEBUG');

        $output->write(
            \sprintf(
                "\n<fg=white;options=bold>%s</> <fg=green;options=bold>%s</>%s%s\n\n",
                $name,
                $version,
                $appEnv !== false ? "  <fg=white;options=bold>Environment:</> {$appEnv}" : '',
                $appDebug !== false ? " <fg=white;options=bold>Debug:</> {$appDebug}" : ''
            )
        );
    }

    /**
     * Describes the application title.
     */
    private function describeUsage(OutputInterface $output): void
    {
        $binary = Application::cerebroBinary();

        $output->write("<fg=yellow;options=bold>USAGE:</> {$binary} <command> [options] [arguments]\n\n");
        $output->write("where <command> is one of:\n");
    }

    /**
     * Describes the application commands.
     */
    private function describeCommands(Application $application, AbstractCommand $command, array $options): void
    {
        $description = new ApplicationDescription(
            $application,
            $options['namespace'] ?? null,
            $options['show-hidden'] ?? false
        );

        Table::setStyleDefinition('zero', self::getZeroBorderStyle());

        $rows = [];
        $namespaceSortedCommandInfos = $this->getNamespaceSortedCommandInfos(
            $description->getCommands()
        );

        foreach ($namespaceSortedCommandInfos as $namespace => $infos) {
            $stringCommands = '';
            $stringDescriptions = '';

            foreach ($infos as $info) {
                $description = '';

                if (isset($options['show-description']) ? (bool) $options['show-description'] : false) {
                    $description = $info['description'];
                }

                $stringCommands .= '<fg=green>' . $info['command'] . "</>\n";
                $stringDescriptions .= $description . "\n";
            }

            $rows[] = [$namespace, $stringCommands, $stringDescriptions];
        }

        $command->table([], $rows, 'zero');
    }

    /**
     * Sort all application commands on namespace.
     */
    private function getNamespaceSortedCommandInfos(array $commands): array
    {
        $namespaceSortedInfos = [];
        $regex = '/^(.*)\:/';
        $binary = Application::cerebroBinary();

        /** @var AbstractCommand $command */
        foreach ($commands as $name => $command) {
            \preg_match($regex, $name, $matches, \PREG_OFFSET_CAPTURE);

            $commandInfo = [
                'command' => $binary . ' ' . $command->getSynopsis(),
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

    private static function getZeroBorderStyle(): TableStyle
    {
        $style = new TableStyle();
        $style->setHorizontalBorderChars(' ');
        $style->setVerticalBorderChars(' ');
        $style->setDefaultCrossingChar(' ');

        return $style;
    }
}
