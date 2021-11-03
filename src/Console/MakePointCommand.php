<?php

namespace JawabApp\Gamify\Console;

use Illuminate\Console\GeneratorCommand;

class MakePointCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gamify:point {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a Gamify point type class.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Point';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/point.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace The root namespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Gamify\Points';
    }
}
