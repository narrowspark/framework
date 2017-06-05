<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests;

use  Viserio\Component\OptionsResolver\Tests\Fixtures\OptionsResolver;

class OptionsResolverTest extends AbstractOptionsResolverTest
{
    protected function getOptionsResolver($class, $data, string $id = null)
    {
        return (new OptionsResolver())->configure($class, $data)->resolve($id);
    }
}
