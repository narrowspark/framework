<?php
declare(strict_types=1);

use LaravelDoctrine\Fluent\EntityMapping;
use LaravelDoctrine\Fluent\Fluent;
use LaravelDoctrine\Fluent\FluentDriver;

if (class_exists(EntityMapping::class)) {
    class_alias(EntityMapping::class, 'Viserio\Bridge\Doctrine\Fluent\EntityMapping');
}

if (class_exists(Fluent::class)) {
    class_alias(Fluent::class, 'Viserio\Bridge\Doctrine\Fluent\Fluent');
}

if (class_exists(FluentDriver::class)) {
    class_alias(FluentDriver::class, 'Viserio\Bridge\Doctrine\Fluent\FluentDriver');
}
