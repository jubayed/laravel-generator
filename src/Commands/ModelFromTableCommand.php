<?php

namespace Jubayed\LaravelGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class ModelFromTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ducor:modelfromtable
                            {--table= : a single table or a list of tables separated by a comma (,)}
                            {--connection= : database connection to use, leave off and it will use the .env connection}
                            {--debug= : turns on debugging}
                            {--folder= : by default models are stored in app, but you can change that}
                            {--namespace= : by default the namespace that will be applied to all models is App}
                            {--singular : class name and class file name singular or plural}
                            {--all= : run for all tables}
                            {--overwrite= : overwrite model(s) if exists}
                            {--module= : use module system}
                            {--timestamps= : whether to timestamp or not}';
                            

                               
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
        $this->call('ducor:modelfrommysql', [
            '--table'           => $this->option('table'),
            '--connection'      => $this->option('connection'),
            '--folder'          => $this->option('folder'),
            '--namespace'       => $this->option('namespace'),
            '--singular'        => $this->option('singular'),
            '--all'             => $this->option('all'),
            '--overwrite'       => $this->option('overwrite'),
            '--timestamps'      => $this->option('timestamps'),            
        ]);
        return 0;
    }


}
