<?php

namespace Jubayed\LaravelGenerator\Commands;

use Illuminate\Console\Command;

class MvcFromModelCommand extends Command
{    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ducor:mvcfrommodel {--model= : Model name}';

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

        $this->call('g:controllerfrommodel', [
            '--model' => $this->getModelNameSpace()
        ]);

        $this->call('g:viewformmodel', [
            'name' => 'index', 
            '--model' => $this->getModelNameSpace()
        ]);
        $this->call('g:viewformmodel', [
            'name' => 'create', 
            '--model' => $this->getModelNameSpace()
        ]);
        $this->call('g:viewformmodel', [
            'name' => 'edit', 
            '--model' => $this->getModelNameSpace()
        ]);
        $this->call('g:viewformmodel', [
            'name' => 'show', 
            '--model' => $this->getModelNameSpace()
        ]);
     
        return 0;
    }


}
