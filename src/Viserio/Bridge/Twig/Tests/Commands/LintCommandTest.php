<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Commands;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Narrowspark\TestingHelper\ArrayContainer;
use Viserio\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Twig_Environment;
use Twig_Loader_Filesystem;
use Viserio\Bridge\Twig\Commands\LintCommand;
use Viserio\Bridge\Twig\Loader;
use Viserio\Component\Contracts\View\Finder as FinderContract;
use Viserio\Component\View\ViewFinder;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;

class LintCommandTest extends TestCase
{
    use MockeryTrait;

    private $files;
    public function setUp()
    {
        $this->files = array();
    }

    public function tearDown()
    {
        parent::tearDown();

        foreach ($this->files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testLintCorrectFile()
    {
        $tester = $this->createCommandTester();
        $filename = $this->createFile('{{ foo }}');
        $ret = $tester->execute(array('filename' => array($filename)), array('verbosity' => OutputInterface::VERBOSITY_VERBOSE, 'decorated' => false));

        self::assertEquals(0, $ret, 'Returns 0 in case of success');
        self::assertContains('OK in', trim($tester->getDisplay()));
    }

    public function testLintIncorrectFile()
    {
        $tester = $this->createCommandTester();
        $filename = $this->createFile('{{ foo');
        $ret = $tester->execute(array('filename' => array($filename)), array('decorated' => false));

        self::assertEquals(1, $ret, 'Returns 1 in case of error');
        self::assertRegExp('/ERROR  in \S+ \(line /', trim($tester->getDisplay()));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testLintFileNotReadable()
    {
        $tester = $this->createCommandTester();

        $filename = $this->createFile('');

        unlink($filename);

        $ret = $tester->execute(array('filename' => array($filename)), array('decorated' => false));
    }

    public function testLintFileCompileTimeException()
    {
        $tester = $this->createCommandTester();
        $filename = $this->createFile("{{ 2|number_format(2, decimal_point='.', ',') }}");
        $ret = $tester->execute(array('filename' => array($filename)), array('decorated' => false));

        self::assertEquals(1, $ret, 'Returns 1 in case of error');
        self::assertRegExp('/ERROR  in \S+ \(line /', trim($tester->getDisplay()));
    }

    /**
     * @return CommandTester
     */
    private function createCommandTester()
    {
        $files  = $this->mock(FilesystemContract::class);
        $config = [
            'config' => [
                'viserio' => [
                    'view' => [
                        'paths' => []
                    ]
                ]
            ],
        ];
        $finder = new ViewFinder($files, $config);
        $twig   = new Twig_Environment(new Loader($finder));

        $application = new Application(
            new ArrayContainer(
                array_merge(
                    $config,
                    [
                        Twig_Environment::class => $twig,
                        FinderContract::class => $finder,
                    ]
                )
            ),
            '1'
        );
        $application->add(new LintCommand());

        return new CommandTester($application->find('twig:lint'));
    }

    /**
     * @return string Path to the new file
     */
    private function createFile($content)
    {
        $filename = tempnam(sys_get_temp_dir(), 'sf-');

        file_put_contents($filename, $content);

        $this->files[] = $filename;

        return $filename;
    }
}
