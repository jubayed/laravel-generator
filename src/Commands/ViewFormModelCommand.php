<?php

namespace Jubayed\LaravelGenerator\Commands;

use Illuminate\Console\GeneratorCommand;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;


class ViewFormModelCommand extends GeneratorCommand
{

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Blade';
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = "ducor:viewformmodel";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    private function chekcModel()
    {
        if (!class_exists($this->option('model'))) {
            $this->error('Model Not Found! "' . $this->option('model'). '::class"');
            exit();
        }

    }

    public function handle()
    {
        $this->chekcModel();

        // First we need to ensure that the given name is not a reserved word within the PHP
        // language and that the class name will actually be valid. If it is not valid we
        // can error now and prevent from polluting the filesystem using invalid files.
        if ($this->isReservedName($this->getNameInput())) {
            $this->error('The name "' . $this->getNameInput() . '" is reserved by PHP.');

            return false;
        }
        $this->backup();



        $path = $this->getDirPath();


        // Next, We will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if ((!$this->hasOption('force') ||
            !$this->option('force')) &&
            $this->alreadyExists($this->getNameInput())
        ) {
            $this->error($this->type . ' already exists!');

            return false;
        }


        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);
        $template = $this->blankTemplate($this->getStub());

        $this->files->put($this->getFullPath(), $template);

        $this->info($this->type . ' created successfully.');
    }
    /**
     * Determine if the class already exists.
     *
     * @param  string  $path
     * @return bool
     */
    protected function alreadyExists($path)
    {
        return $this->files->exists($path);
    }
   
    protected function backup()
    {
        $name = $this->argument('name');

        if ( $this->option('backup') && file_exists($this->getFullPath())) {
            $timestamp = date('d_His');
            if (!copy($this->getFullPath(), $this->getFullPath() . '_' . $timestamp . '.~')) {
                $this->info('Backup Fails');
            } else {
                $this->info("$name.blade.php backup complete");
            }
        } 
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function blankTemplate($subject)
    {
        return $this->stub([
            '{{ layout }}' => Config::get('laravel-generator.template.layout'),
            '{{ page_title }}' => $this->getAttr('page_title'),
            '{{ breadcrumb_title }}' => $this->getAttrLang('breadcrumb_title'),
            '{{ breadcrumb_subtitle }}' => $this->getAttrLang('breadcrumb_subtitle'),
            '{{ breadcrumb_route }}' => $this->getBreadcrumbRoute(),
            '{{ breadcrumb_item_one }}' => $this->getAttrLang('breadcrumb_item_one'),
            '{{ breadcrumb_item_two }}' => $this->getAttrLang('breadcrumb_item_two'),
            '{{ title }}' => $this->getAttrLang('title'),
            '{{ table }}' => $this->indexTemplate(),
            '{{ create_form }}' => $this->createTemplate(),
            '{{ edit_form }}' => $this->editTemplate(),
            '{{ show_form }}' => $this->showTemplate(),
            
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
        $name = strtolower($this->argument('name'));

        if( $name == 'index'){
            $stub = __DIR__.'/../../stubs/view/index.stub';
        }elseif ( $name =='create' ) {
            $stub = __DIR__.'/../../stubs/view/create.stub';
        }elseif ( $name == 'edit') {
            $stub = __DIR__.'/../../stubs/view/edit.stub';
        }elseif ( $name == 'show') {
            $stub = __DIR__.'/../../stubs/view/show.stub';
        }else {
            $stub = __DIR__.'/../../stubs/view/blank.stub';
        }
        return file_get_contents($stub) ;
    }

    protected function getModelName()
    {
        $model = $this->getModelNameSpace();
        $model = \explode("\\", $model);
        return end($model);
    }

    protected function getModelNameSpace()
    {
        $model = $this->option('model');

        if(class_exists($model) ){
            return $model;
            //$file = new \ReflectionClass( $model );
            //return $file->getFileName();
        }
        $this->info("Class Not Found");

    }

    protected function getDirPath()
    {
        $name = Config::get('laravel-generator.view.path');

        $name .= $this->getModelName();
        $name = Str::plural(strtolower($name));

        return resource_path('views/'. $name);
    }


    protected function getFullPath()
    {
        $path = $this->getDirPath();
        $path .= '/' . strtolower($this->argument('name')) . '.blade.php';

        return $path;
    }


    private function getAttr($key)
    {
        $model = strtolower($this->getModelName());
        if($key == 'breadcrumb_item_one'){
            $name = $this->getModelName();
            $name = Str::plural(strtolower($name));
            $key = 'breadcrumb.'. $name;
        }elseif($key == 'breadcrumb_item_two'){
            $name = $this->argument('name');
            $name = strtolower($name);
            $key = 'breadcrumb.'. $name;
        }
        elseif(Str::contains($key, 'breadcrumb_')){
            $key = 'breadcrumb.'. str_replace('breadcrumb_', '', $key );
        }
        return $model.'.'.$key;
        
    }

    private function getAttrLang($key)
    {
        $key = $this->getAttr($key);
        return '{!! ___( "' . $key .'" ) !!}';
    }

    private function getBreadcrumbRoute()
    {
        $name = $this->getModelName();
        $name = Str::plural(strtolower($name));

        $index = strtolower($this->argument('name'));

        return '{{ route( "' . $name. '.'.$index . '" ) }}';       
    }

    private function indexTemplate()
    {
        // model
        $model = $this->option('model');
        $model = new $model();
        $fillables = $model->getFillable();

        $t = "                ";
        $template = $t. "<table ".'data-toggle="table" data-search="true" data-show-columns="true" data-checkbox-header="false" data-height="460" data-pagination="true" data-sort-name="name" data-sort-order="desc" data-show-toggle="true" data-show-fullscreen="true"   data-locale="en-USA"'.">\r\n";
        // thead
        $template .=  $t. "  <thead>\r\n";
        // get data
        $name = $this->getModelName();
        $name = Str::plural(strtolower($name));
        $index = strtolower($this->argument('name'));
        $route = '$'. $name . '->' . $index;

        foreach ($fillables as $key => $fillable ) {
            $th = $name. '.' .$index .'.table.'.$fillable;
            if($key == 0){
                $template .=  $t. "     <th scope='col'> {{ __( '".$name. '.' .$index .'.table.id'."' ) }}</th>\r\n";
            }
            $template .=  $t. "     <th scope='col'> {{ __( '".$th."' ) }}</th>\r\n";
            if($key + 1 == count($fillables)){
                $template .=  $t. "     <th scope='col'> {{ __( '".$name. '.' .$index .'.table.handle'."' ) }}</th>\r\n";
            }
        }
        $template .=  $t. "  </thead>\r\n";
        // tbody
        $template .=  $t. "  <tbody>\r\n";
        $template .=  $t. "    @foreach(".'$data as $key => $value'.")\r\n";
        $template .=  $t. "      <tr>\r\n";
        foreach ($fillables as $key => $fillable ) {
            $th = $name. '.' .$index .'.table.'.$fillable;
            if($key == 0){
                $template .=  $t. "        <td>{{ ".'$value->id' ." }}</td>\r\n";
            }
            $template .=  $t. "        <td>{{ ".'$value->'.$fillable." }}</td>\r\n";
            if($key + 1 == count($fillables)){
                $template .=  $t. "        <td>\r\n";
                $template .=  $t. "          ".'<x-core::show :route="route('.$route.', $value->id)" />'."\r\n";
                $template .=  $t. "          ".'<x-core::edit :route="route('.$route.', $value->id)" />'."\r\n";
                $template .=  $t. "          ".'<x-core::delete :route="route('.$route.', $value->id)" />'."\r\n";
                $template .=  $t. "        </td>\r\n";
            }
        }
        $template .=  $t. "      </tr>\r\n";
        $template .=  $t. "    @endforeach\r\n";
        
        $template .=  $t. "  </tbody>\r\n";
        //end table
        $template .=  $t. "</table>";
        return $template;
    }

    private function createTemplate()
    {
        
        // model
        $model = $this->option('model');
        $model = new $model();
        $fillables = $model->getFillable();

        $tab = "                  ";        
        // thead
        $template = '';
        // get data
        $name = $this->getModelName();
        $name = Str::singular(strtolower($name));

        foreach ($fillables as $key => $fillable ) {
            if ($key == 0) {
                $template .=  $tab . "     @csrf\r\n";
            }
            $template .=  $tab. '  <x-core::fields.text name="'. $fillable .'" value="">'."\r\n";
            $template .=  $tab .'     {!! ___( "' . $name . '.create.field.' . $fillable . '.label" ) !!}'."\r\n";
            $template .=  $tab. '     <x-slot name="help">'."\r\n";
            $template .=  $tab. '     {!! ___( "'.$name.'.create.field.'.$fillable.'.hint" ) !!}'. "\r\n";
            $template .=  $tab. '     </x-slot>'. "\r\n";
            $template .=  $tab. "  </x-core::fields.text>\r\n";
        }
        return $template;
    }

    private function editTemplate()
    {

        // model
        $model = $this->option('model');
        $model = new $model();
        $fillables = $model->getFillable();

        $tab = "                ";
        $template = "";
        // thead

        // get data
        $name = $this->getModelName();
        $name = Str::singular(strtolower($name));

        foreach ($fillables as $key => $fillable) {
            if ($key == 0) {
                $template .=  $tab . "     @csrf\r\n";
            }
            $template .=  $tab . '  <x-core::fields.text name="' . $fillable . '" value="{{ $data->'.$fillable.' }}">' . "\r\n";
            $template .=  $tab . '     {!! ___( "' . $name . '.create.field.' . $fillable . '.label" ) !!}' . "\r\n";
            $template .=  $tab . '     <x-slot name="help">' . "\r\n";
            $template .=  $tab . '     {!! ___( "' . $name . '.create.field.' . $fillable . '.hint" ) !!}' . "\r\n";
            $template .=  $tab . '     </x-slot>' . "\r\n";
            $template .=  $tab . "  </x-core::fields.text>\r\n";
        }

        return $template;

    }


    private function showTemplate()
    {
        // model
        $model = $this->option('model');
        $model = new $model();
        $fillables = $model->getFillable();

        $tab = "                ";
        $template = "";
        // get data
        $name = $this->getModelName();
        $name = Str::singular(strtolower($name));

        foreach ($fillables as $key => $fillable) {
            $template .=  $tab . '<x-core::fields.text name="' . $fillable . '" value="{{ $data->' . $fillable . ' }}" readonly>' . "\r\n";
            $template .=  $tab . '   {!! ___( "' . $name . '.create.field.' . $fillable . '.label" ) !!}' . "\r\n";
            $template .=  $tab . "</x-core::fields.text>\r\n";
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
            ['name', InputArgument::REQUIRED, 'The name of the blade file'],
        ];
    }


}
