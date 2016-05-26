<?php
namespace Viserio\Support\Providers;

use Narrowspark\Arr\StaticArr as Arr;
use RandomLib\Factory as RandomLib;
use Viserio\Application\ServiceProvider;
use Viserio\Support\AliasLoader;
use Viserio\Support\Str;

class SupportServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->registerArr();
        $this->registerStr();
        $this->registerStaticalProxyResolver();
        $this->registerAliasLoader();
    }

    public function aliases()
    {
        return ['rand' => 'RandomLib\Factory'];
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides(): array
    {
        return [
            'arr',
            'helper',
            'rand',
            'rand.generator',
            'str',
            'statical.resolver',
        ];
    }

    /**
     * Register Arr.
     *
     * @return \Narrowspark\Arr\StaticArr as Arr|null
     */
    protected function registerArr(): \Narrowspark\Arr\StaticArr
    {
        $this->app->singleton('arr', function () {
            return new Arr();
        });
    }

    /**
     * Register Str.
     *
     * @return \Viserio\Support\Str|null
     */
    protected function registerStr()
    {
        $this->app->singleton('str', function () {
            return new Str();
        });
    }

    /**
     * Register randomlib.
     *
     * @return \RandomLib\Factory|null
     */
    protected function registerRand()
    {
        $this->app->singleton('rand', function () {
            return new RandomLib();
        });
    }

    /**
     * Register randomlib generator.
     *
     * @return \RandomLib\Factory|null
     */
    protected function registerRandGenerator()
    {
        $this->app->bind('rand.generator', function ($app) {
            $generatorStrength = ucfirst(
                $app->get('config')->get(
                    'app::generator.strength',
                    'Medium'
                )
            );

            $generator = sprintf('get%sStrengthGenerator', $generatorStrength);

            return $app->get('rand')->$generator();
        });
    }

    protected function registerAliasLoader()
    {
        $this->app->singleton('alias', function () {
            return new AliasLoader();
        });
    }
}
