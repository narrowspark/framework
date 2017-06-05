<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Commands;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Twig\Environment;
use Twig\Loader\LoaderInterface;
use Viserio\Bridge\Twig\Commands\DebugCommand;
use Viserio\Bridge\Twig\Loader;
use Viserio\Component\Console\Application;
use Viserio\Component\Contracts\View\Finder as FinderContract;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\View\ViewFinder;

class DebugCommandTest extends MockeryTestCase
{
    public function testThrowErrorIfTwigIsNotSet()
    {
        $config = [
            'config' => [
                'viserio' => [
                    'view' => [
                        'paths' => [
                            $path ?? __DIR__ . '/../Fixtures/',
                        ],
                    ],
                ],
            ],
        ];
        $finder = new ViewFinder(new Filesystem(), new ArrayContainer($config));
        $loader = new Loader($finder);
        $twig   = new Environment($loader);

        $application = new Application('1');
        $application->setContainer(new ArrayContainer(
            array_merge(
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

        self::assertSame('The Twig environment needs to be set.', trim($tester->getDisplay(true)));
    }

    public function testDebug()
    {
        $tester   = $this->createCommandTester();
        $ret      = $tester->execute([], ['decorated' => false]);

        self::assertTrue(is_string($tester->getDisplay(true)));
    }

    public function testDebugJsonFormat()
    {
        $tester   = $this->createCommandTester();
        $ret      = $tester->execute(['--format' => 'json'], ['decorated' => false]);

        self::assertTrue(is_string($tester->getDisplay(true)));
    }

    /**
     * @return CommandTester
     */
    private function createCommandTester()
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
        $loader = new Loader($finder);
        $twig   = new Environment($loader);

        $application = new Application('1');
        $application->setContainer(new ArrayContainer(
            array_merge(
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
