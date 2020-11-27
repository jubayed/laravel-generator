<?php

namespace Jubayed\LaravelGenerator\Commands;

use Illuminate\Console\Command;

class MvcFromTableCommand extends Command
{    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ducor:mvcfromtable
                            {--table= : a single table or a list of tables separated by a comma (,)}
                            {--connection= : database connection to use, leave off and it will use the .env connection}
                            {--debug= : turns on debugging}
                            {--module= : module name}
                            {--overwrite= : overwrite model(s) if exists}
                            {--timestamps= : whether to timestamp or not}';

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
        $data = array(
            '--table' => $this->option('table')
        );
        $mode = array();


        if ($this->option('connection')) {
            $data['--connection'] = $this->option('connection');
        }

        if ($this->option('overwrite')) {
            $data['--overwrite'] = $this->option('overwrite');
        }

        if ($this->option('timestamps')) {
            $data['--timestamps'] = $this->option('timestamps');
        }

        $this->call('ducor:modelfromtable', $data);

        // $this->call('ducor:controllerfrommodel', [
        //     '--model' => $this->getModelNameSpace()
        // ]);

        // $this->call('ducor:viewformmodel', [
        //     'name' => 'index', 
        //     '--model' => $this->getModelNameSpace()
        // ]);
        // $this->call('ducor:viewformmodel', [
        //     'name' => 'create', 
        //     '--model' => $this->getModelNameSpace()
        // ]);
        // $this->call('ducor:viewformmodel', [
        //     'name' => 'edit', 
        //     '--model' => $this->getModelNameSpace()
        // ]);
        // $this->call('ducor:viewformmodel', [
        //     'name' => 'show', 
        //     '--model' => $this->getModelNameSpace()
        // ]);
     
        return 0;
    }


    public function getModelNameSpace()
    {
        $namespace = $this->option('table');

        return $namespace;
    }


}
