<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Testing;

use Doctrine\Common\Persistence\ManagerRegistry;
use Faker\Generator as Faker;
use InvalidArgumentException;
use Narrowspark\Collection\Collection;

class FactoryBuilder
{
    /**
     * The model definitions in the container.
     *
     * @var array
     */
    protected $definitions = [];

    /**
     * The model being built.
     *
     * @var string
     */
    protected $class;

    /**
     * The name of the model being built.
     *
     * @var string
     */
    protected $name = 'default';

    /**
     * The number of models to build.
     *
     * @var int
     */
    protected $amount = 1;

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
     * Create an new builder instance.
     *
     * @param \Doctrine\Common\Persistence\ManagerRegistry $registry
     * @param string                                       $class
     * @param string                                       $name
     * @param array                                        $definitions
     * @param \Faker\Generator                             $faker
     */
    public function __construct(ManagerRegistry $registry, string $class, string $name, array $definitions, Faker $faker)
    {
        $this->name        = $name;
        $this->class       = $class;
        $this->faker       = $faker;
        $this->registry    = $registry;
        $this->definitions = $definitions;
    }

    /**
     * Set the amount of models you wish to create / make.
     *
     * @param int $amount
     *
     * @return $this
     */
    public function times(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Create a collection of models and persist them to the database.
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function create(array $attributes = [])
    {
        $results = $this->make($attributes);
        $manager = $this->registry->getManagerForClass($this->class);

        if ($this->amount === 1) {
            $manager->persist($results);
        } else {
            foreach ($results as $result) {
                $manager->persist($result);
            }
        }

        $manager->flush();

        return $results;
    }

    /**
     * Create a collection of models.
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function make(array $attributes = [])
    {
        if ($this->amount === 1) {
            return $this->makeInstance($attributes);
        }

        $results = [];

        for ($i = 0; $i < $this->amount; ++$i) {
            $results[] = $this->makeInstance($attributes);
        }

        return new Collection($results);
    }

    /**
     * Make an instance of the model with the given attributes.
     *
     * @param array $attributes
     *
     * @return mixed
     */
    protected function makeInstance(array $attributes = [])
    {
        if (! isset($this->definitions[$this->class][$this->name])) {
            throw new InvalidArgumentException("Unable to locate factory with name [{$this->name}].");
        }

        $definition = call_user_func($this->definitions[$this->class][$this->name], $this->faker, $attributes);

        if ($definition instanceof $this->class) {
            return $definition;
        }

        return SimpleHydrator::hydrate($this->class, array_merge($definition, $attributes));
    }
}
