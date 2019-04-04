<?php

namespace MorningTrain\Laravel\Resources\Console;


use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CrudResourceMakeCommand extends GeneratorCommand
{

    protected $name        = 'mt-make:crud-resource';
    protected $description = 'Create and setup a new CrudResource';
    protected $type        = 'CrudResource';

    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'Full path of the Model'],
        ];
    }

    protected function getOptions()
    {
        return [
            ['name', null, InputOption::VALUE_OPTIONAL, 'Resource name. Default will be model name.'],

            ['namespace', null, InputOption::VALUE_OPTIONAL, 'Namespace name. Default: "api"', 'api'],
        ];
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Resources\\' . ucfirst($this->option('namespace'));
    }

    protected function getNameInput()
    {
        return $this->option('name') ?? class_basename(trim($this->argument('model')));
    }

    public function handle()
    {
        if (parent::handle() === false) {
            return false;
        }

        $this->addToConfig();
    }

    protected function addToConfig()
    {
        // Check if config exists // TODO - check for actuall file
        if (!is_array(config('resources'))) {
            $this->error('Could not find "resources" config file. Make sure you run vendor:publish.');

            return false;
        }

        // Add to config // TODO
    }

    protected function buildClass($name)
    {
        $class = parent::buildClass($name);

        $class = str_replace('DummyModel', $this->argument('model'), $class);

        return $class;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/crud-resource.stub';
    }

}
