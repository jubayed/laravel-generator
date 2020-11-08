<?php

namespace Jubayed\LaravelGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class GenerateModelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ducor:make-model 
                            {table : Database tablename}     
                            {--c|controller=false : Generate Controller}
                            {--r|view=false : Generate blade in view}';
                           
    
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

        $exitCode = $this->call('code:models', [
           '--table' => $this->argument('table')
        ]);
        $this->info("model Generated");

        if($this->option('controller') == null || $this->option('controller') == true ){
            
            $this->call('ducor:make-controller', [
                '--model' => $this->modelNamespace(),
            ]);
        }

        if($this->option('view') == null || $this->option('view') == true ){
            
            $this->call('ducor:make-view', [
                'name' => 'index',
                '--model' => $this->modelNamespace(),
            ]);
            $this->call('ducor:make-view', [
                'name' => 'create',
                '--model' => $this->modelNamespace(),
            ]);
            $this->call('ducor:make-view', [
                'name' => 'edit',
                '--model' => $this->modelNamespace(),
            ]);
            $this->call('ducor:make-view', [
                'name' => 'show',
                '--model' => $this->modelNamespace(),
            ]);
            
        }

        
        //$this->info($exitCode);
        return 0;
    }


    public function move()
    {
        $table = $this->argument('table');

        $table = Str::ucfirst(Str::studly($table));

        $model_file = Config::get('models.*.path');
        $timestamp = date('d_His');
        //singular
        $absolate_path = $model_file. DIRECTORY_SEPARATOR. Str::singular($table) . '.php';
        if(file_exists($absolate_path)){
            rename($absolate_path, $absolate_path. '_'.$timestamp .'~' );
        }
    }

    public function getModelPath()
    {
        $name = $this->argument('table');
        $model_path = Config::get('models.*.path');
        $model_path .= '/'.Str::ucfirst(Str::studly( Str::plural($name) ));
        return $model_path;
    }

    public function modelNamespace()
    {
        $name = $this->argument('table');
        return  Config::get('laravel-generator.namespace.model'). '\\'. Str::singular($name);
    }
}
