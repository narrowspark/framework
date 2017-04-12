<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Tests\DataCollectors;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Log\DataCollectors\LogParser;
use Viserio\Component\Log\DataCollectors\LogsDataCollector;

class LogsDataCollectorTest extends MockeryTestCase
{
    public function testAddMessageAndLog()
    {
        $collector = new LogsDataCollector(new LogParser(), [__DIR__ . '/../Fixture/']);
        $collector->addMessage('foobar');

        $msgs = $collector->getMessages();

        static::assertCount(1, $msgs);

        $collector->addMessage(['hello'], 'notice');

        static::assertCount(1, $collector->getMessages());

        $collector->flush();

        $msgs = $collector->getMessages();

        static::assertCount(1, $msgs);
    }

    public function testCollect()
    {
        $collector = new LogsDataCollector(new LogParser(), [__DIR__ . '/../Fixture/']);
        $collector->addMessage('foo');

        $collector->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        $data = $collector->getData();

        static::assertEquals(1, $data['counted']);
        static::assertEquals(1, $collector->getCountedLogs());
        static::assertEquals($collector->getMessages(), $data['messages']);
    }

    public function testGetMenu()
    {
        $collector = new LogsDataCollector(new LogParser(), [__DIR__ . '/../Fixture/']);

        $collector->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        static::assertSame(['label' => 'Logs', 'value' => 1], $collector->getMenu());
    }

    public function testGetPanel()
    {
        $collector = new LogsDataCollector(new LogParser(), [__DIR__ . '/../Fixture/']);

        $collector->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        static::assertSame(
            preg_replace('/(\r\n|\n\r|\r)/', "\n", '<select class="content-selector" name="logs-data-collector"><option selected>test</option></select><div  class="selected-content"><table class="row"><thead><tr><th scope="col" class="Type">Type</th><th scope="col" class="Message">Message</th></tr></thead><tbody><tr><td><pre class=sf-dump data-indent-pad="  ">"<span class=sf-dump-str title="5 characters">error</span>"
</pre>
</td><td><pre class=sf-dump data-indent-pad="  ">"<span class=sf-dump-str title="2065 characters">[2017-01-03 18:58:03] develop.ERROR: Viserio\Contracts\Container\Exceptions\NotFoundException: Abstract (Viserio\Translation\DataCollectors\ViserioTranslationDataCollector) is not being managed by the container in \src\Viserio\Container\Container.php:378 Stack trace: #0 \src\Viserio\WebProfiler\Providers\WebProfilerServiceProvider.php(131): Viserio\Container\Container-&gt;get(&#039;Viserio\\\\Transla...&#039;) #1 \src\Viserio\WebProfiler\Providers\WebProfilerServiceProvider.php(68): Viserio\WebProfiler\Providers\WebProfilerServiceProvider::registerCollectorsFromConfig(Object(Viserio\Foundation\Kernel), Object(Viserio\WebProfiler\WebProfiler)) #2 \src\Viserio\Container\Container.php(433): Viserio\WebProfiler\Providers\WebProfilerServiceProvider::createWebProfiler(Object(Viserio\Foundation\Kernel), NULL) #3 [internal function]: Viserio\Container\Container::Viserio\Container\{closure}(Object(Viserio\Foundation\Kernel)) #4 \src\Viserio\Container\ContainerResolver.php(131): ReflectionFunction-&gt;invokeArgs(Array) #5 \src\Viserio\Container\ContainerResolver.php(37): Viserio\Container\ContainerResolver-&gt;resolveFunction(Object(Closure), Array) #6 \src\Viserio\Container\Container.php(621): Viserio\Container\ContainerResolver-&gt;resolve(Object(Closure), Array) #7 \src\Viserio\Container\Container.php(260): Viserio\Container\Container-&gt;resolveSingleton(&#039;Viserio\\\\Contrac...&#039;, Array) #8 \src\Viserio\Container\Container.php(232): Viserio\Container\Container-&gt;resolveBound(&#039;Viserio\\\\Contrac...&#039;, Array) #9 \src\Viserio\Container\Container.php(373): Viserio\Container\Container-&gt;resolve(&#039;Viserio\\\\Contrac...&#039;) #10 \src\Viserio\Foundation\Http\Kernel.php(216): Viserio\Container\Container-&gt;get(&#039;Viserio\\\\Contrac...&#039;) #11 \src\Viserio\Foundation\Http\Kernel.php(174): Viserio\Foundation\Http\Kernel-&gt;handleRequest(Object(Viserio\Http\ServerRequest)) #12 D:\Anolilab\Github\Php\narrowspark\public\index.php(36): Viserio\Foundation\Http\Kernel-&gt;handle(Object(Viserio\Http\ServerRequest)) #13 {main} {&quot;identification&quot;:{}} []</span>"
</pre>
</td></tr></tbody></table></div>'),
            preg_replace('/(\r\n|\n\r|\r)/', "\n", $this->removeSomeValues($collector->getPanel()))
        );
    }

    private function removeSomeValues(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/', '', $html);
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/', '', $html);
        $html = preg_replace('/value="content-(.*?)"/', '', $html);
        $html = preg_replace('/id="content-(.*?)"/', '', $html);
        $html = preg_replace('/id=sf-dump-(?:\d+) /', '', $html);

        return trim(preg_replace('/&quot;id&quot;:&quot;(.*?)&quot;/', '', $html));
    }
}
