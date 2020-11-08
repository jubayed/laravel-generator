<?php

namespace Jubayed\LaravelGenerator\Commands\Scaffold;

use Illuminate\Console\Command;

class GenerateMvcCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:mvc {name}';  
    
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
        $this->call('ducor:make-model', [
            'table' => $this->argument('name'),
            '-c' => true,
            '-r' => true,
        ]);

        $this->info('MVC Generated');
        return 0;
    }    
}
