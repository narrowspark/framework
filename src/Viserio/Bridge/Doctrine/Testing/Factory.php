<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Testing;

use ArrayAccess;
use Doctrine\Common\Persistence\ManagerRegistry;
use Faker\Generator as Faker;
use Symfony\Component\Finder\Finder;

class Factory implements ArrayAccess
{
    /**
     * The Faker instance for the builder.
     *
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * The doctrine manager registry instance.
     *
     * @var \Doctrine\Common\Persistence\ManagerRegistry
     */
    protected $registry;

    /**
     * The model definitions in the container.
     *
     * @var array
     */
    protected $definitions = [];

    /**
     * Create a new factory instance.
     *
     * @param \Faker\Generator                             $faker
     * @param \Doctrine\Common\Persistence\ManagerRegistry $registry
     * @param string                                       $pathToFactories
     */
    public function __construct(Faker $faker, ManagerRegistry $registry, string $pathToFactories)
    {
        $this->faker    = $faker;
        $this->registry = $registry;

        if (! is_dir($pathToFactories)) {
            throw new RuntimeException(sprintf('[%s] is not a directory.', $pathToFactories));
        }

        foreach (Finder::create()->files()->name('*.php')->in($pathToFactories) as $file) {
            require $file->getRealPath();
        }
    }

    /**
     * Define a class with a given short-name.
     *
     * @param string   $class
     * @param string   $name
     * @param callable $attributes
     *
     * @return void
     */
    public function defineAs(string $class, string $name, callable $attributes): void
    {
        $this->define($class, $attributes, $name);
    }

    /**
     * Define a class with a given set of attributes.
     *
     * @param string   $class
     * @param callable $attributes
     * @param string   $name
     *
     * @return void
     */
    public function define(string $class, callable $attributes, string $name = 'default'): void
    {
        $this->definitions[$class][$name] = $attributes;
    }

    /**
     * Create an instance of the given model and persist it to the database.
     *
     * @param string $class
     * @param array  $attributes
     *
     * @return mixed
     */
    public function create(string $class, array $attributes = [])
    {
        return $this->of($class)->create($attributes);
    }

    /**
     * Create an instance of the given model and type and persist it to the database.
     *
     * @param string $class
     * @param string $name
     * @param array  $attributes
     *
     * @return mixed
     */
    public function createAs(string $class, string $name, array $attributes = [])
    {
        return $this->of($class, $name)->create($attributes);
    }

    /**
     * Create an instance of the given model.
     *
     * @param string $class
     * @param array  $attributes
     *
     * @return mixed
     */
    public function make(string $class, array $attributes = [])
    {
        return $this->of($class)->make($attributes);
    }

    /**
     * Create an instance of the given model and type.
     *
     * @param string $class
     * @param string $name
     * @param array  $attributes
     *
     * @return mixed
     */
    public function makeAs(string $class, string $name, array $attributes = [])
    {
        return $this->of($class, $name)->make($attributes);
    }

    /**
     * Get the raw attribute array for a given named model.
     *
     * @param string $class
     * @param string $name
     * @param array  $attributes
     *
     * @return array
     */
    public function rawOf(string $class, string $name, array $attributes = [])
    {
        return $this->raw($class, $attributes, $name);
    }

    /**
     * Get the raw attribute array for a given model.
     *
     * @param string $class
     * @param array  $attributes
     * @param string $name
     *
     * @return array
     */
    public function raw(string $class, array $attributes = [], string $name = 'default'): array
    {
        $raw = call_user_func($this->definitions[$class][$name], $this->faker);

        return array_merge($raw, $attributes);
    }

    /**
     * Create a builder for the given model.
     *
     * @param string $class
     * @param string $name
     *
     * @return \Viserio\Bridge\Doctrine\Testing\FactoryBuilder
     */
    public function of(string $class, string $name = 'default'): FactoryBuilder
    {
        return new FactoryBuilder($this->registry, $class, $name, $this->definitions, $this->faker);
    }

    /**
     * Determine if the given offset exists.
     *
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->definitions[$offset]);
    }

    /**
     * Get the value of the given offset.
     *
     * @param string $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->make($offset);
    }

    /**
     * Set the given offset to the given value.
     *
     * @param string   $offset
     * @param callable $value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        return $this->define($offset, $value);
    }

    /**
     * Unset the value at the given offset.
     *
     * @param string $offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->definitions[$offset]);
    }
}
