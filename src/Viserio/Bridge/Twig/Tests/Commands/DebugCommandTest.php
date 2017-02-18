<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Commands;

use Mockery as Mock;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Twig_Environment;
use Twig_LoaderInterface;
use Viserio\Bridge\Twig\Commands\DebugCommand;
use Viserio\Bridge\Twig\Loader;
use Viserio\Component\Console\Application;
use Viserio\Component\Contracts\View\Finder as FinderContract;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\View\ViewFinder;

class DebugCommandTest extends TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

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
        $twig   = new Twig_Environment($loader);

        $application = new Application(
            new ArrayContainer(
                array_merge(
                    $config,
                    [
                        FinderContract::class       => $finder,
                        Twig_LoaderInterface::class => $loader,
                    ]
                )
            ),
            '1'
        );
        $application->add(new DebugCommand());

        $tester = new CommandTester($application->find('twig:debug'));

        $tester->execute([], ['decorated' => false]);

        self::assertSame('The Twig environment needs to be set.', trim($tester->getDisplay()));
    }

    public function testDebug()
    {
        $tester   = $this->createCommandTester();
        $ret      = $tester->execute([], ['decorated' => false]);

        self::assertSame(str_replace("\r\n", '', trim('Functions--------- * constant(constant, object = null) * cycle(values, position) * date(date = null, timezone = null) * include(template, variables = [], withContext = true, ignoreMissing = false, sandboxed = false) * max(args) * min(args) * random(values = null) * range(low, high, step) * source(name, ignoreMissing = false)Filters------- * abs * batch(size, fill = null) * capitalize * convert_encoding(to, from) * date(format = null, timezone = null) * date_modify(modifier) * default(default = "") * e(strategy = "html", charset = null, autoescape = false) * escape(strategy = "html", charset = null, autoescape = false) * first * format(args) * join(glue = "") * json_encode(options, depth) * keys * last * length * lower * merge(arr2) * nl2br(is_xhtml) * number_format(decimal = null, decimalPoint = null, thousandSep = null) * raw * replace(from) * reverse(preserveKeys = false) * round(precision = 0, method = "common") * slice(start, length = null, preserveKeys = false) * sort * split(delimiter, limit = null) * striptags(allowable_tags) * title * trim(character_mask) * upper * url_encodeTests----- * constant * defined * divisible by * empty * even * iterable * none * null * odd * same as')), str_replace("\r\n", '', trim($tester->getDisplay())));
    }

    public function testDebugJsonFormat()
    {
        $tester   = $this->createCommandTester();
        $ret      = $tester->execute(['--format' => 'json'], ['decorated' => false]);

        self::assertSame(str_replace("\r\n", '', trim('{"functions":{"max":["args"],"min":["args"],"range":["low","high","step"],"constant":["constant","object = null"],"cycle":["values","position"],"random":{"1":"values = null"},"date":{"1":"date = null","2":"timezone = null"},"include":{"2":"template","3":"variables = []","4":"withContext = true","5":"ignoreMissing = false","6":"sandboxed = false"},"source":{"1":"name","2":"ignoreMissing = false"}},"filters":{"date":["format = null","timezone = null"],"date_modify":["modifier"],"format":["args"],"replace":["from"],"number_format":["decimal = null","decimalPoint = null","thousandSep = null"],"abs":[],"round":["precision = 0","method = \"common\""],"url_encode":[],"json_encode":["options","depth"],"convert_encoding":["to","from"],"title":[],"capitalize":[],"upper":[],"lower":[],"striptags":["allowable_tags"],"trim":["character_mask"],"nl2br":["is_xhtml"],"join":["glue = \"\""],"split":["delimiter","limit = null"],"sort":[],"merge":["arr2"],"batch":["size","fill = null"],"reverse":["preserveKeys = false"],"length":[],"slice":["start","length = null","preserveKeys = false"],"first":[],"last":[],"default":["default = \"\""],"keys":[],"escape":["strategy = \"html\"","charset = null","autoescape = false"],"e":["strategy = \"html\"","charset = null","autoescape = false"],"raw":[]},"tests":["even","odd","defined","same as","none","null","divisible by","constant","empty","iterable"]}')), str_replace("\r\n", '', trim($tester->getDisplay())));
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
        $twig   = new Twig_Environment($loader);

        $application = new Application(
            new ArrayContainer(
                array_merge(
                    $config,
                    [
                        Twig_Environment::class     => $twig,
                        FinderContract::class       => $finder,
                        Twig_LoaderInterface::class => $loader,
                    ]
                )
            ),
            '1'
        );
        $application->add(new DebugCommand());

        return new CommandTester($application->find('twig:debug'));
    }
}
