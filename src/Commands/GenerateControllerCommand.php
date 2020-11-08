<?php

namespace Jubayed\LaravelGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class GenerateControllerCommand extends Command
{    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ducor:make-controller {--m|model= : Model name}
                            {--r|resource= : Generate Resource}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->move();

        $template = $this->buildClass($this->getStub());
        file_put_contents($this->getAbsolutePath(), $template);
        $this->info('Controller Generated');

        if($this->option('resource')){

            $exitCode = Artisan::call('ducor:make-view', [
                'name' => 'index', '--model' => $this->getModelNameSpace()
            ]);
            $exitCode = Artisan::call('ducor:make-view', [
                'name' => 'create', '--model' => $this->getModelNameSpace()
            ]);
            $exitCode = Artisan::call('ducor:make-view', [
                'name' => 'edit', '--model' => $this->getModelNameSpace()
            ]);
            $exitCode = Artisan::call('ducor:make-view', [
                'name' => 'show', '--model' => $this->getModelNameSpace()
            ]);
        }

        return 0;
    }


    public function move()
    {
        $path = $this->getAbsolutePath();
        $timestamp = date('d_His');
        //singular
        if(file_exists($path)){
            rename($path, $path. '_'.$timestamp .'~' );
        }
    }

    public function getAbsolutePath()
    {
        $path = Config::get('laravel-generator.path.controller');
        $path .= $this->getControllerName(). '.php';
        return $path;
    }

    /**
     * @return array|string
     */
    protected function getControllerName()
    {
        $controller = $this->getModelName();
        $controller .= 'Controller';
        return $controller;
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
        $stub = __DIR__.'/../../../stubs/controller.stub';
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
        die("");
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
        $fillables = $model->getFillable();

        $t = '          ';
        $template = '';

        foreach ($fillables as $key => $fillable ) {
            $template .=  $t. '//"'.$fillable.'" => ""';
            if($key+1 != count($fillables) ){
                $template .= ",\r\n";
            }
        }

        return $template;
    }
    
}
