<?php
declare(strict_types=1);
namespace Viserio\Provider\Twig\Tests\Command;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Support\Invoker;
use Viserio\Provider\Twig\Command\CleanCommand;

/**
 * @internal
 */
final class CleanCommandTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Console\Command\AbstractCommand
     */
    private $command;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $path = __DIR__ . '/Fixture/test';

        @\mkdir($path);

        $command = new CleanCommand(
            [
                'viserio' => [
                    'view' => [
                        'engines' => [
                            'twig' => [
                                'options' => [
                                    'cache' => $path,
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        $command->setInvoker(new Invoker());

        $this->command = $command;
    }

    public function testSuccess(): void
    {
        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        static::assertContains('Twig cache cleaned.', $output);
    }
}
