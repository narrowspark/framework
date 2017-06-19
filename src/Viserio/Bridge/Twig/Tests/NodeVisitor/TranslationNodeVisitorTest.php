<?php
declare(strict_types=1);
namespace Viserio\Provider\Twig\Tests\NodeVisitor;

use Viserio\Bridge\Twig\NodeVisitor\TranslationNodeVisitor;
use Twig\Environment;
use Twig\Node\Expression\NameExpression;
use Twig\Node\BodyNode;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FilterExpression;
use Twig\Node\ModuleNode;
use Twig\Node\Node;
use Twig\Source;
use Viserio\Bridge\Twig\Node\TransNode;
use PHPUnit\Framework\TestCase;
use Twig\Loader\LoaderInterface;

class TranslationNodeVisitorTest extends TestCase
{
    /**
     * @dataProvider getMessagesExtractionTestData
     */
    public function testMessagesExtraction(Node $node, array $expectedMessages)
    {
        $env = new Environment($this->getMockBuilder(LoaderInterface::class)->getMock(), array('cache' => false, 'autoescape' => false, 'optimizations' => 0));
        $visitor = new TranslationNodeVisitor();
        $visitor->enable();
        $visitor->enterNode($node, $env);
        $visitor->leaveNode($node, $env);

        self::assertEquals($expectedMessages, $visitor->getMessages());
    }

    public function testMessageExtractionWithInvalidDomainNode()
    {
        $message = 'new key';
        $node = new FilterExpression(
            new ConstantExpression($message, 0),
            new ConstantExpression('trans', 0),
            new Node(array(
                new ArrayExpression(array(), 0),
                new NameExpression('variable', 0),
            )),
            0
        );

        $this->testMessagesExtraction($node, array(array($message, '_undefined')));
    }

    public function getMessagesExtractionTestData()
    {
        $message = 'new key';
        $domain = 'domain';

        return array(
            array(self::getTransFilter($message), array(array($message, null))),
            array(self::getTransTag($message), array(array($message, null))),
            array(self::getTransFilter($message, $domain), array(array($message, $domain))),
            array(self::getTransTag($message, $domain), array(array($message, $domain))),
        );
    }

    private static function getModule($content)
    {
        return new ModuleNode(
            new ConstantExpression($content, 0),
            null,
            new ArrayExpression([], 0),
            new ArrayExpression([], 0),
            new ArrayExpression([], 0),
            null,
            new Source('', '')
        );
    }

    private static function getTransFilter($message, $domain = null, $arguments = null)
    {
        if (!$arguments) {
            $arguments = $domain ? array(
                new ArrayExpression([], 0),
                new ConstantExpression($domain, 0),
            ) : [];
        }

        return new FilterExpression(
            new ConstantExpression($message, 0),
            new ConstantExpression('trans', 0),
            new Node($arguments),
            0
        );
    }

    private static function getTransTag($message, $domain = null)
    {
        return new TransNode(
            new BodyNode([], array('data' => $message)),
            $domain ? new ConstantExpression($domain, 0) : null
        );
    }
}
