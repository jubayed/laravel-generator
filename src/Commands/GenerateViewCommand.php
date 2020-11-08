<?php

namespace Jubayed\LaravelGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;

class GenerateViewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ducor:make-view
                            { name : name of blade file in view}
                            {--modelnamespace|model= : Enter model namespace *required}';

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
        $template = $this->blankTemplate($this->getStub());
        
        $name =  $this->argument('name');

        if (file_exists($this->getViewPath())) {
            $timestamp = date('d_His');
            if (!copy($this->getViewPath(), $this->getViewPath(). '_'. $timestamp. '.~')) {
                $this->info('File copy problem');
            }else{
                file_put_contents($this->getViewPath(), $template);
                $this->info($name . 'View Generated');
            }
        }else{
            file_put_contents($this->getViewPath(), $template);
            $this->info($name . ' View Generated');
        }
        

        return 0;
    }

    
    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    private function blankTemplate($subject)
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
    private function stub($target = [], $subject)
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
    private function getStub()
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

    private function getModelName()
    {
        $model = $this->getModelNameSpace();
        $model = \explode("\\", $model);
        return end($model);
    }

    private function getModelNameSpace()
    {
        $model = $this->option('model');

        if(class_exists($model) ){
            return $model;
            //$file = new \ReflectionClass( $model );
            //return $file->getFileName();
        }
        $this->info("Class Not Found");

    }

    private function getViewPath()
    {
        $name = Config::get('laravel-generator.view.path');

        $name .= $this->getModelName();
        $name = Str::plural(strtolower($name));

        if(is_dir(resource_path( 'views/'. $name ))){
            //$this->error("View path already exist");
            //die();
        }else{
            File::makeDirectory( resource_path('views/'. $name ));
        }

        $path =  $name. '/'. strtolower( $this->argument('name') ) . '.blade.php';

        return resource_path('views/'. $path);
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

        if(!class_exists($this->option('model'))){
            return dd("model Not found");
        }
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
        
        
        if(!class_exists($this->option('model'))){
            return dd("model Not found");
        }
        // model
        $model = $this->option('model');
        $model = new $model();
        $fillables = $model->getFillable();

        $t = "                ";
        $template = $t. "<form>\r\n";
        // thead

        // get data
        $name = $this->getModelName();
        $name = Str::plural(strtolower($name));

        foreach ($fillables as $key => $fillable ) {
            $template .=  $t. "  <div class='form-row'>\r\n";
            $template .=  $t. "    <div class='form-group col'>\r\n";

            if($key == 0){
                $template .=  $t. "     @csrf\r\n";
            }
            $template .=  $t. '     <label for="field-'.$fillable.'">{!! ___( "'.$name.'.create.field.'.$fillable.'.label" ) !!}</label>'."\r\n";
            $template .=  $t. '     <input name="'.$fillable.'" value="{{ old( "'.$fillable.'" ) }}" id="field-'.$fillable.'" class="form-control @if($errors->has("'.$fillable.'"))  is-invalid @endif"  aria-describedby="field-help-'.$fillable.'" />'."\r\n";
            $template .=  $t. "     @if($"."errors->has('".$fillable."'))\r\n";
            $template .=  $t. '     <small id="field-help-'.$fillable.'" class="invalid-feedback">{!! ___( $errors->first("'.$fillable.'") ) !!}</small>'. "\r\n";
            $template .=  $t. "     @else\r\n";
            $template .=  $t. '     <small id="field-help-'.$fillable.'" class="form-text text-muted">{!! ___( "'.$name.'.create.field.'.$fillable.'.hint" ) !!}</small>'. "\r\n";
            $template .=  $t. "     @endif\r\n";

            $template .=  $t. "    </div>\r\n";
            $template .=  $t. "  </div>\r\n\\r\n";
        }
        $template .=  $t. "</form>\r\n";
        

        return $template;
    }

    private function editTemplate()
    {
        
        if(!class_exists($this->option('model'))){
            return dd("model Not found");
        }
        // model
        $model = $this->option('model');
        $model = new $model();
        $fillables = $model->getFillable();

        $t = "                ";
        $template = $t. "<form>\r\n";
        // thead

        // get data
        $name = $this->getModelName();
        $name = Str::plural(strtolower($name));

        foreach ($fillables as $key => $fillable ) {
            $template .=  $t. "  <div class='form-row'>\r\n";
            $template .=  $t. "    <div class='form-group col'>\r\n";

            if($key == 0){
                $template .=  $t. "     @csrf\r\n";
            }
            $template .=  $t. '     <label for="field-'.$fillable.'">{!! ___( "'.$name.'.create.field.'.$fillable.'.label" ) !!}</label>'."\r\n";
            $template .=  $t. '     <input name="'.$fillable.'" value="{{ old( "'.$fillable.'", $data->'.$fillable.') }}" id="field-'.$fillable.'" class="form-control @if($errors->has("'.$fillable.'"))  is-invalid @endif"  aria-describedby="field-help-'.$fillable.'" />'."\r\n";
            $template .=  $t. "     @if($"."errors->has('".$fillable."'))\r\n";
            $template .=  $t. '     <small id="field-help-'.$fillable.'" class="invalid-feedback">{!! ___( $errors->first("'.$fillable.'") ) !!}</small>'. "\r\n";
            $template .=  $t. "     @else\r\n";
            $template .=  $t. '     <small id="field-help-'.$fillable.'" class="form-text text-muted">{!! ___( "'.$name.'.create.field.'.$fillable.'.hint" ) !!}</small>'. "\r\n";
            $template .=  $t. "     @endif\r\n";

            $template .=  $t. "    </div>\r\n";
            $template .=  $t. "  </div>\r\n\r\n";
        }
        $template .=  $t. "</form>\r\n";
        

        return $template;

    }


    private function showTemplate()
    {
        
        
        if(!class_exists($this->option('model'))){
            return dd("model Not found");
        }
        // model
        $model = $this->option('model');
        $model = new $model();
        $fillables = $model->getFillable();

        $t = "                ";
        $template = "";
        // get data
        $name = $this->getModelName();
        $name = Str::plural(strtolower($name));

        foreach ($fillables as $fillable ) {
            $template .=  $t. "<div class='form-row'>\r\n";
            $template .=  $t. "  <div class='form-group col'>\r\n";

            $template .=  $t. '    <label for="field-'.$fillable.'" class="col-sm-2 col-form-label">{!! ___( "'.$name.'.create.field.'.$fillable.'.label" ) !!}</label>'."\r\n";
            $template .=  $t. '    <div class="col-sm-10">'. "\r\n";
            $template .=  $t. '      <input name="'.$fillable.'" value="{{ $data->'.$fillable.' }}" id="field-'.$fillable.'" class="form-control" />'."\r\n";
            $template .=  $t. "    </div>\r\n";

            $template .=  $t. "  </div>\r\n";
            $template .=  $t. "</div>\r\n\r\n";
        }
        
        return $template;
    }

}
