<?php
declare(strict_types=1);
namespace Viserio\Provider\Twig\Tests\NodeVisitor;

use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\LoaderInterface;
use Twig\Node\BodyNode;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FilterExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Node\Node;
use Viserio\Bridge\Twig\Node\TransNode;
use Viserio\Bridge\Twig\NodeVisitor\TranslationNodeVisitor;

class TranslationNodeVisitorTest extends TestCase
{
    /**
     * @dataProvider getMessagesExtractionTestData
     *
     * @param Node  $node
     * @param array $expectedMessages
     */
    public function testMessagesExtraction(Node $node, array $expectedMessages)
    {
        $env     = new Environment($this->getMockBuilder(LoaderInterface::class)->getMock(), ['cache' => false, 'autoescape' => false, 'optimizations' => 0]);
        $visitor = new TranslationNodeVisitor();
        $visitor->enable();
        $visitor->enterNode($node, $env);
        $visitor->leaveNode($node, $env);

        self::assertEquals($expectedMessages, $visitor->getMessages());
    }

    public function testMessageExtractionWithInvalidDomainNode()
    {
        $message = 'new key';
        $node    = new FilterExpression(
            new ConstantExpression($message, 0),
            new ConstantExpression('trans', 0),
            new Node([
                new ArrayExpression([], 0),
                new NameExpression('variable', 0),
            ]),
            0
        );

        $this->testMessagesExtraction($node, [[$message, '_undefined']]);
    }

    public function getMessagesExtractionTestData()
    {
        $message = 'new key';
        $domain  = 'domain';

        return [
            [self::getTransFilter($message), [[$message, null]]],
            [self::getTransTag($message), [[$message, null]]],
            [self::getTransFilter($message, $domain), [[$message, $domain]]],
            [self::getTransTag($message, $domain), [[$message, $domain]]],
        ];
    }

    private static function getTransFilter($message, $domain = null, $arguments = null)
    {
        if (! $arguments) {
            $arguments = $domain ? [
                new ArrayExpression([], 0),
                new ConstantExpression($domain, 0),
            ] : [];
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
            new BodyNode([], ['data' => $message]),
            $domain ? new ConstantExpression($domain, 0) : null
        );
    }
}
