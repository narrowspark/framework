<?php

declare(strict_types=1);
namespace Viserio\View\Tests;

use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\Events\Dispatcher as EventDispatcher;
use Viserio\View\Virtuoso;

class VirtuosoTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testGetDispatcher()
    {
        $virtuoso = $this->getVirtuoso();

        $this->assertInstanceOf(EventDispatcher::class, $virtuoso->getDispatcher());
    }

    public function testBasicSectionHandling()
    {
        $virtuoso = $this->getVirtuoso();

        $virtuoso->startSection('foo');
        echo 'hi';
        $virtuoso->stopSection();

        $this->assertEquals('hi', $virtuoso->yieldContent('foo'));
    }

    public function testSectionExtending()
    {
        $virtuoso = $this->getVirtuoso();

        $virtuoso->startSection('foo');
        echo 'hi @parent';
        $virtuoso->stopSection();

        $virtuoso->startSection('foo');
        echo 'there';
        $virtuoso->stopSection();

        $this->assertEquals('hi there', $virtuoso->yieldContent('foo'));
    }

    public function testSectionMultipleExtending()
    {
        $virtuoso = $this->getVirtuoso();

        $virtuoso->startSection('foo');
        echo 'hello @parent nice to see you @parent';
        $virtuoso->stopSection();

        $virtuoso->startSection('foo');
        echo 'my @parent';
        $virtuoso->stopSection();

        $virtuoso->startSection('foo');
        echo 'friend';
        $virtuoso->stopSection();

        $this->assertEquals('hello my friend nice to see you my friend', $virtuoso->yieldContent('foo'));
    }

    public function testSingleStackPush()
    {
        $virtuoso = $this->getVirtuoso();

        $virtuoso->startSection('foo');
        echo 'hi';
        $virtuoso->appendSection();

        $this->assertEquals('hi', $virtuoso->yieldContent('foo'));
    }

    public function testMultipleStackPush()
    {
        $virtuoso = $this->getVirtuoso();

        $virtuoso->startSection('foo');
        echo 'hi';
        $virtuoso->appendSection();

        $virtuoso->startSection('foo');
        echo ', Hello!';
        $virtuoso->appendSection();

        $this->assertEquals('hi, Hello!', $virtuoso->yieldContent('foo'));
    }

    public function testSessionAppending()
    {
        $virtuoso = $this->getVirtuoso();

        $virtuoso->startSection('foo');
        echo 'hi';
        $virtuoso->appendSection();

        $virtuoso->startSection('foo');
        echo 'there';
        $virtuoso->appendSection();

        $this->assertEquals('hithere', $virtuoso->yieldContent('foo'));
    }

    public function testYieldSectionStopsAndYields()
    {
        $virtuoso = $this->getVirtuoso();

        $virtuoso->startSection('foo');
        echo 'hi';

        $this->assertEquals('hi', $virtuoso->yieldSection());
    }

    public function testInjectStartsSectionWithContent()
    {
        $virtuoso = $this->getVirtuoso();
        $virtuoso->inject('foo', 'hi');

        $this->assertEquals('hi', $virtuoso->yieldContent('foo'));
    }

    public function testEmptyStringIsReturnedForNonSections()
    {
        $virtuoso = $this->getVirtuoso();

        $this->assertEmpty($virtuoso->yieldContent('foo'));
    }

    public function testSectionclearing()
    {
        $virtuoso = $this->getVirtuoso();

        $virtuoso->startSection('foo');
        echo 'hi';
        $virtuoso->stopSection();

        $this->assertCount(1, $virtuoso->getSections());

        $virtuoso->clearSections();
        $this->assertCount(0, $virtuoso->getSections());
    }

    public function testHasSection()
    {
        $virtuoso = $this->getVirtuoso();

        $virtuoso->startSection('foo');
        echo 'hi';
        $virtuoso->stopSection();

        $this->assertTrue($virtuoso->hasSection('foo'));
        $this->assertFalse($virtuoso->hasSection('bar'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Cannot end a section without first starting one.
     */
    public function testExtraStopSectionCallThrowsException()
    {
        $virtuoso = $this->getVirtuoso();
        $virtuoso->startSection('foo');
        $virtuoso->stopSection();
        $virtuoso->stopSection();
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Cannot end a section without first starting one.
     */
    public function testExtraAppendSectionCallThrowsException()
    {
        $virtuoso = $this->getVirtuoso();

        $virtuoso->startSection('foo');
        $virtuoso->stopSection();
        $virtuoso->appendSection();
    }

    protected function getVirtuoso()
    {
        $container = $this->mock(ContainerInterface::class);

        return new Virtuoso(
            $container,
            $this->mock(EventDispatcher::class)
        );
    }
}
