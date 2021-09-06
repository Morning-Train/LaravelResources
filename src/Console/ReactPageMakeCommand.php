<?php

namespace MorningTrain\Laravel\Resources\Console;


use Illuminate\Console\GeneratorCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ReactPageMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:react-page';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create and setup a new Eloquent Resource';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'React Page';

    /**
     * Execute the console command.
     *
     * @return bool|null
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        if (parent::handle() === false) {
            return false;
        }
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/react-page.stub';
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass($name)
    {
        //$name = class_basename(Str::ucfirst($name));

        $component = $this->option('component');
        if(!$component) {
            $component = 'Path.To.React.Component';
        }

        $path = $this->option('path');
        if(!$path) {
            $path = '/path/to/page';
        }

        $replace = [
            //'{{ name }}' => $name,
            //'{{name}}' => $name,
            '{{ component }}' => $component,
            '{{component}}' => $component,
            '{{ path }}' => $path,
            '{{path}}' => $path,
        ];

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\Http\\Operations\\App';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            //['name', 'n', InputOption::VALUE_OPTIONAL, 'The name of the page'],
            ['component', 'c', InputOption::VALUE_OPTIONAL, 'The React component of the page'],
            ['path', 'p', InputOption::VALUE_OPTIONAL, 'The path of the page'],
        ];
    }

}
