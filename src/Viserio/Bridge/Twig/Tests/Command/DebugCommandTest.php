<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\LoaderInterface;
use Viserio\Bridge\Twig\Command\DebugCommand;
use Viserio\Component\Console\Application;
use Viserio\Component\Contracts\View\Finder as FinderContract;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\View\ViewFinder;

class DebugCommandTest extends MockeryTestCase
{
    public function testThrowErrorIfTwigIsNotSet(): void
    {
        $config = [
            'config' => [
                'viserio' => [
                    'view' => [
                        'paths' => [
                            __DIR__ . '/../Fixtures/',
                        ],
                    ],
                ],
            ],
        ];
        $finder = new ViewFinder(new Filesystem(), new ArrayContainer($config));
        $loader = new ArrayLoader([]);

        $application = new Application('1');
        $application->setContainer(new ArrayContainer(
            \array_merge(
                $config,
                [
                    FinderContract::class       => $finder,
                    LoaderInterface::class      => $loader,
                ]
            )
        ));
        $application->add(new DebugCommand());

        $tester = new CommandTester($application->find('twig:debug'));

        $tester->execute([], ['decorated' => false]);

        self::assertSame('The Twig environment needs to be set.', \trim($tester->getDisplay(true)));
    }

    public function testDebug(): void
    {
        $tester   = $this->createCommandTester();
        $tester->execute([], ['decorated' => false]);

        self::assertTrue(\is_string($tester->getDisplay(true)));
    }

    public function testDebugJsonFormat(): void
    {
        $tester   = $this->createCommandTester();
        $tester->execute(['--format' => 'json'], ['decorated' => false]);

        self::assertTrue(\is_string($tester->getDisplay(true)));
    }

    /**
     * @return CommandTester
     */
    private function createCommandTester(): \Symfony\Component\Console\Tester\CommandTester
    {
        $config = [
            'config' => [
                'viserio' => [
                    'view' => [
                        'paths' => [
                            __DIR__ . '/../Fixtures/',
                        ],
                    ],
                ],
            ],
        ];
        $finder = new ViewFinder(new Filesystem(), new ArrayContainer($config));
        $loader = new ArrayLoader([]);
        $twig   = new Environment($loader);

        $application = new Application('1');
        $application->setContainer(new ArrayContainer(
            \array_merge(
                $config,
                [
                    Environment::class          => $twig,
                    FinderContract::class       => $finder,
                    LoaderInterface::class      => $loader,
                ]
            )
        ));
        $application->add(new DebugCommand());

        return new CommandTester($application->find('twig:debug'));
    }
}
