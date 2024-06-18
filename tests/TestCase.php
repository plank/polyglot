<?php

namespace Plank\Polyglot\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Plank\Polyglot\PolyglotServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        //Factory::guessFactoryNamesUsing(
        //    fn (string $modelName) => 'Plank\\Polyglot\\Database\\Factories\\'.class_basename($modelName).'Factory'
        //);
    }

    protected function getPackageProviders($app)
    {
        return [
            PolyglotServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_polyglot_table.php.stub';
        $migration->up();
        */
    }
}
