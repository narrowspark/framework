<?php
declare(strict_types=1);

use LaravelDoctrine\Fluent\EntityMapping;
use LaravelDoctrine\Fluent\Fluent;
use LaravelDoctrine\Fluent\FluentDriver;

class_alias(EntityMapping::class, 'Viserio\Bridge\Doctrine\Fluent\EntityMapping');
class_alias(Fluent::class, 'Viserio\Bridge\Doctrine\Fluent\Fluent');
class_alias(FluentDriver::class, 'Viserio\Bridge\Doctrine\Fluent\FluentDriver');
