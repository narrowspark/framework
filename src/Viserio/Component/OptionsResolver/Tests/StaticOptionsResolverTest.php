<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests;

use  Viserio\Component\OptionsResolver\Tests\Fixtures\StaticOptionsResolver;

class StaticOptionsResolverTest extends AbstractOptionsResolverTest
{
    protected function getOptionsResolver($class, $data, string $id = null)
    {
        return (new StaticOptionsResolver())->configure($class, $data)->resolve($id);
    }
}
