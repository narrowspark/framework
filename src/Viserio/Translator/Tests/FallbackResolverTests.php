<?php
namespace Viserio\Translator\Tests;

use Viserio\Translator\FallbackResolver;

class FallbackResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAndSetFallback()
    {
        $fallback = new FallbackResolver();

        $locales = [
            'en_US',
            'de_DE'
        ];

        $fallback->setFallbackLocales($locales);

        $this->assertEquals($locales, $fallback->getFallbackLocales());
    }
}
