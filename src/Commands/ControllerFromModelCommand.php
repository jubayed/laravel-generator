<?php

namespace Jubayed\LaravelGenerator\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ControllerFromModelCommand extends GeneratorCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = "ducor:controllerfrommodel";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $type = "Controller";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    private function chekcModel()
    {
        if($this->option('model')){
            $this->error("Model Class Required");
        }
        if (!class_exists($this->option('model'))) {
            
            $this->error('Model Not Found! "' . $this->option('model') . '::class"');
            exit();
        }
    }


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        // First we need to ensure that the given name is not a reserved word within the PHP
        // language and that the class name will actually be valid. If it is not valid we
        // can error now and prevent from polluting the filesystem using invalid files.
        if ($this->isReservedName($this->getNameInput())) {
            $this->error('The name "' . $this->getNameInput() . '" is reserved by PHP.');

            return false;
        }
        $this->backup();

        // Next, We will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if (!$this->option('force') && file_exists($this->getFullPath())) {
            $this->error($this->type . ' already exists!');

            return false;
        }


        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.

        $template = $this->buildClass($this->getStub());

        $this->files->put($this->getFullPath(), $template);

        $this->info($this->type . ' created successfully.');
        return 0;
    }

    protected function backup()
    {
        $name = $this->argument('name');

        if ($this->option('backup') && file_exists($this->getFullPath())) {
            $timestamp = date('d_His');
            if (!copy($this->getFullPath(), $this->getFullPath() . '_' . $timestamp . '.~')) {
                $this->info('Backup Fails');
            } else {
                $this->info("$name.blade.php backup complete");
            }
        }
    }

    protected function getDirPath()
    {
        return  Config::get('laravel-generator.path.controller');
    }

    protected function controllerName()
    {
        $name = $this->argument('name');
        if(!$name){
            $name = $this->getModelName();
        }

        $controller = Str::studly($name);

        if (Str::contains(strtolower($controller), 'controller') === false) {
            $controller .= 'Controller';
        }

        return $controller;
    }

    protected function getFullPath()
    {
        $path = $this->getDirPath();
        $path .= $this->getAbsolutePath();
        return $path;
    }

    public function getAbsolutePath()
    {
        $path = $this->controllerName(). '.php';
        return $path;
    }

    /**
     * @return array|string
     */
    protected function getControllerName()
    {
        $controller = $this->getModelName();
        $controller .= 'Controller';
        return ucfirst($controller);
        //return Config::get('laravel-generator.path.controller'). $controller. '.php';
    }

    
    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($subject)
    {
        
        return $this->stub([
            '{{ namespace }}' => Config::get('laravel-generator.namespace.controller'),
            '{{ controller }}' => $this->getControllerName(),
            '{{ modelName }}' => $this->getModelName(),
            '{{ model_namespace }}' => $this->getModelNameSpace(),
            '{{ path }}' => $this->getViewPath(),
            '{{ field_data }}' => $this->createData(),
            '{{ edit_data }}' => $this->editData(),
            '{{ notify }}' =>  $this->notify(),
            '{{ field_validate }}' =>  $this->validataData(),
            
            
        ], $subject);
    }

    
    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function stub($target = [], $subject)
    {
        foreach ($target as $key => $value) {
            $subject = str_replace(  $key,  $value,  $subject );
        }
        return  $subject;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $stub = __DIR__.'/../../stubs/controller.stub';
        return file_get_contents($stub) ;
    }

    public function getModelName()
    {
        $model = $this->getModelNameSpace();
        $model = \explode("\\", $model);
        $model = end($model);

        $class = Str::ucfirst(Str::studly($model));

        $model_file = Config::get('models.*.path');
        $absolate_path = $model_file. DIRECTORY_SEPARATOR. $class . '.php';

        if(!file_exists($absolate_path)){
            $this->info("Model Not found");
            die();
        }
        return $model;
    }

    public function getModelNameSpace()
    {
        $model = $this->option('model');
        if(class_exists($model) ){
            return $model;
           
            //$file = new \ReflectionClass( $model );
            //return $file->getFileName();
        }
        $this->info("Class Not Found");
        exit();
    }

    public function getViewPath()
    {
        $name = Config::get('laravel-generator.view.path');
        $name .= $this->getModelName();
        $name = Str::plural(strtolower($name));

        return $name;
    }

    public function createData()
    {
        $model = $this->option('model');
        $model = new $model();
        $fillables = $model->getFillable();

        $t = '          ';
        $template = '';

        foreach ($fillables as $key => $fillable ) {
            $template .=  $t. '"'.$fillable.'" => $request->'. $fillable ;
            if($key+1 != count($fillables) ){
                $template .= ",\r\n";
            }
        }

        

        return $template;
    }

    public function editData()
    {
        # code...
    }

    public function notify()
    {
        return strtolower( Str::singular($this->getModelName()) );
    }


    public function validataData()
    {
        $model = $this->option('model');
        $model = new $model();
        $fillables = $model->getCasts();



        $t = '          ';
        $template = '';
        $counter = 1;
        foreach ($fillables as $key => $fillable ) {
            $counter++;

            $value = 'required';

            if(in_array($key, ['id', 'created_at', 'updated_at', 'delete_at'])){
                continue;
            }

            if ($fillable == 'int') {
                $value .= '|integer';
            }

            if ($fillable == 'string') {
                $value .= '|string';
            }

            $template .=  $t. '"'. $key. '" => "'.$value. '"';

            if($counter != count($fillables) ){
                $template .= ",\r\n";
            }
            

        }

        return $template;
    }


    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['model', null, InputOption::VALUE_REQUIRED, 'Enter model namespace'],
            ['force', 'f', InputOption::VALUE_NONE, 'overwrite view(s) if exists'],
            ['backup', 'b', InputOption::VALUE_NONE, 'backup view(s) if exists'],
        ];
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::OPTIONAL, 'The name of the Controller file'],
        ];
    }
}
