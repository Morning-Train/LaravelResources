<?php

namespace MorningTrain\Laravel\Resources\Console;


use Illuminate\Console\GeneratorCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class EloquentResourceMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'mt-make:eloquent-resource';

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
    protected $type = 'EloquentResource';

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

        $this->addToConfig();
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/eloquent-resource.stub';
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
        $resource = class_basename(Str::ucfirst($name));

        $namespaceModel = $this->option('model')
            ? $this->qualifyModel($this->option('model'))
            : $this->qualifyModel($this->guessModelName($name));

        $model = class_basename($namespaceModel);

        $replace = [
            'NamespacedDummyModel'    => $namespaceModel,
            '{{ namespacedModel }}'   => $namespaceModel,
            '{{namespacedModel}}'     => $namespaceModel,
            'DummyModel'              => $model,
            '{{ model }}'             => $model,
            '{{model}}'               => $model,
            '{{ resource }}'          => $resource,
            '{{resource}}'            => $resource,
            '{{ fields }}'            => $this->getFields($namespaceModel),
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
        return $rootNamespace.'\\Http\\Resources\\Api';
    }

    protected function addToConfig()
    {
        $configFile = app()->configPath('resources.php');

        if (!file_exists($configFile) || !is_array(config('resources'))) {
            $this->error('Could not find "resources" config file. Make sure you run vendor:publish.');

            return false;
        }

        // Add to config // TODO
    }

    /**
     * Guess the model name from the Resource name or return a default model name.
     *
     * @param  string  $name
     * @return string
     */
    protected function guessModelName($name)
    {
        $modelName = $this->qualifyModel(Str::after($name, 'App\\Http\\Resources\\Api'));

        if (class_exists($modelName)) {
            return $modelName;
        }

        if (is_dir(app_path('Models/'))) {
            return 'App\Models\Model';
        }

        return 'App\Model';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'The name of the model'],
        ];
    }

    /**
     * Return the string template for Resource Fields, based on Model attributes
     *
     * @param string $model
     * @return string
     */
    protected function getFields(string $model): string
    {
        if (!class_exists($model) || !($model = new $model) instanceof Model) {
            return '';
        }

        $rows = rescue(fn() => DB::getDoctrineSchemaManager()
            ->listTableDetails($model->getTable())
            ->getColumns()
        );

        $unwanted = [$model->getKeyName(),
            ...($model->usesTimestamps() ? [
                $model->getCreatedAtColumn(),
                $model->getUpdatedAtColumn(),
            ] : []),
        ];

        return collect($rows)
            ->keys()
            ->diff($unwanted)
            ->map(fn($key) => "            Field::create('{$key}')->validates(''),")
            ->join(PHP_EOL);
    }
}
