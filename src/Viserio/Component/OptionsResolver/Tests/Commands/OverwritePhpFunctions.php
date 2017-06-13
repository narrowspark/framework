<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Commands;

use Viserio\Component\OptionsResolver\Tests\Fixtures\ValidatedConfigurationFixture;

function get_declared_classes(): array
{
    return [
        ValidatedConfigurationFixture::class,
    ];
}
