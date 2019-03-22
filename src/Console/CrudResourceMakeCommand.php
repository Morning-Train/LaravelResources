<?php

namespace MorningTrain\Laravel\Resources\Console;


use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;

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

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Resources\Api';
    }

    protected function getNameInput()
    {
        return class_basename(trim($this->argument('model')));
    }

    public function handle()
    {
        // Check if config exists
            // err & exit: Please publish vendor

        $this->info($this->getPath('test'));

        if (parent::handle() === false) {
            return false;
        }

        // Add to config
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        // TODO: Implement getStub() method.
        return __DIR__.'/stubs/crud-resource.stub';

        throw new \Exception('STOP RIGHT THERE!!');
    }

}
